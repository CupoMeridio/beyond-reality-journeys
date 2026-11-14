/**
 * BeyondReality Journeys - Prenotazione e pagamento con Stripe (solo modalità test).
 *
 * 1. Invio del form di prenotazione -> crea_pagamento.php valida i dati, calcola il prezzo
 *    e crea il PaymentIntent; il popup mostra l'importo calcolato dal server.
 * 2. Conferma nel popup -> Stripe addebita la carta di prova (confirmCardPayment).
 * 3. conferma_prenotazione.php verifica il pagamento con Stripe e salva la prenotazione.
 * 4. Il popup offre il link alla ricevuta PDF: è un clic dell'utente, quindi non viene bloccato.
 */
(() => {
    const formPrenotazione = document.getElementById('booking-form');
    const popup = document.getElementById('popup');
    const pannelloStripe = document.getElementById('pagamento_con_stripe');
    const formPagamento = document.getElementById('payment-form');
    const pannelloCompletato = document.getElementById('pagamento-completato');
    if (!formPrenotazione || !popup || !formPagamento) return;

    const bottonePrenota = document.getElementById('submit-form-button');
    const bottonePaga = document.getElementById('submit-button');
    const erroriCarta = document.getElementById('card-errors');
    const riepilogo = document.getElementById('riepilogo-prenotazione');
    const linkRicevuta = document.getElementById('link-ricevuta');

    let stripe = null;
    let elementoCarta = null;
    let clientSecret = null;
    let idPagamento = null;
    let cartaAddebitata = false; // se la conferma lato server fallisce, si riprova senza riaddebitare

    /** POST form-urlencoded che restituisce sempre il JSON, anche con codici di errore. */
    async function postJson(url, dati) {
        const risposta = await fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams(dati).toString(),
        });
        let json;
        try {
            json = await risposta.json();
        } catch {
            throw new Error('Risposta non valida dal server.');
        }
        if (!risposta.ok || json.error) {
            throw new Error(json.error || `Errore HTTP ${risposta.status}`);
        }
        return json;
    }

    async function caricaStripe(chiavePubblica) {
        if (stripe) return;
        if (!chiavePubblica.startsWith('pk_test_')) {
            throw new Error('Pagamenti disabilitati: il sito funziona solo in modalità test.');
        }
        if (typeof Stripe === 'undefined') {
            await new Promise((resolve, reject) => {
                const script = document.createElement('script');
                script.src = 'https://js.stripe.com/v3/';
                script.onload = resolve;
                script.onerror = () => reject(new Error('Impossibile caricare Stripe.'));
                document.head.appendChild(script);
            });
        }
        stripe = Stripe(chiavePubblica);
        elementoCarta = stripe.elements().create('card');
        elementoCarta.mount('#card-element');
        elementoCarta.on('change', (e) => {
            erroriCarta.textContent = e.error ? e.error.message : '';
        });
    }

    function datiPrenotazione() {
        const fd = new FormData(formPrenotazione);
        const biglietti = parseInt(fd.get('tickets-count'), 10) || 1;
        const nominativi = [];
        for (let i = 0; i < biglietti; i++) {
            nominativi.push(document.getElementById('ticket-name-' + i)?.value ?? '');
        }
        return {
            num_biglietti: biglietti,
            location: fd.get('location') ?? '',
            data_p: fd.get('departure-date') ?? '',
            data_r: fd.get('return-date') ?? '',
            nominativi: JSON.stringify(nominativi),
            commento: fd.get('comments') ?? '',
        };
    }

    function apriPopup() {
        pannelloStripe.style.display = 'block';
        formPagamento.style.display = 'block';
        pannelloCompletato.style.display = 'none';
        popup.classList.add('open');
    }

    function chiudiPopup() {
        popup.classList.remove('open');
        pannelloStripe.style.display = 'none';
        formPagamento.reset();
        elementoCarta?.clear();
        erroriCarta.textContent = '';
        bottonePaga.disabled = false;
        bottonePrenota.disabled = false;
        clientSecret = null;
        idPagamento = null;
        cartaAddebitata = false;
    }

    // 1. Prenota: il server valida, calcola il prezzo e crea il pagamento
    formPrenotazione.addEventListener('submit', async (event) => {
        event.preventDefault();
        bottonePrenota.disabled = true;
        try {
            const risposta = await postJson('api/stripe/crea_pagamento.php', datiPrenotazione());
            await caricaStripe(risposta.publishableKey);
            clientSecret = risposta.clientSecret;
            idPagamento = clientSecret.split('_secret_')[0];
            riepilogo.textContent = `${risposta.riepilogo} · Totale ${risposta.importoFormattato}`;
            bottonePaga.textContent = `Paga ${risposta.importoFormattato}`;
            apriPopup();
        } catch (errore) {
            alert(errore.message);
            bottonePrenota.disabled = false;
        }
    });

    // 2-3. Pagamento con la carta di prova, poi verifica e salvataggio lato server
    formPagamento.addEventListener('submit', async (event) => {
        event.preventDefault();
        if (!clientSecret) return;
        bottonePaga.disabled = true;
        erroriCarta.textContent = '';

        if (!cartaAddebitata) {
            const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
                payment_method: {
                    card: elementoCarta,
                    billing_details: { name: formPagamento.fullname.value },
                },
            });

            if (error) {
                erroriCarta.textContent = `Errore durante il pagamento: ${error.message}`;
                bottonePaga.disabled = false;
                return;
            }
            if (paymentIntent?.status !== 'succeeded') {
                erroriCarta.textContent = 'Il pagamento non è stato completato.';
                bottonePaga.disabled = false;
                return;
            }
            cartaAddebitata = true;
        }

        try {
            const risposta = await postJson('api/stripe/conferma_prenotazione.php', { payment_intent: idPagamento });
            linkRicevuta.href = `api/stripe/genera_ricevuta_pdf.php?id=${encodeURIComponent(risposta.id_prenotazione)}`;
            formPagamento.style.display = 'none';
            pannelloCompletato.style.display = 'block';
            formPrenotazione.reset();
            // rigenera i campi dei passeggeri per il numero di biglietti predefinito
            document.getElementById('tickets-count')?.dispatchEvent(new Event('input'));
        } catch (errore) {
            // La conferma è idempotente: si può riprovare senza rischio di doppie prenotazioni
            erroriCarta.textContent = `${errore.message} Premi di nuovo per riprovare (la carta non verrà riaddebitata).`;
            bottonePaga.disabled = false;
        }
    });

    document.getElementById('close-button').addEventListener('click', chiudiPopup);
    document.getElementById('close-button-completato').addEventListener('click', chiudiPopup);
})();
