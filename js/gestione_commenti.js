/**
 * BeyondReality Journeys - Recensioni (lettura periodica e invio).
 *
 * - Il mondo arriva da <body data-mondo>, non dalla sessione (che con più schede sbaglia).
 * - Le recensioni vengono costruite con textContent: nessun HTML interpretato, nessun doppio escape.
 * - Aggiornamento ogni 30 secondi e solo con la scheda visibile (prima: ogni 5 s, sempre).
 */
const MONDO = document.body.dataset.mondo ?? '';
const INTERVALLO_AGGIORNAMENTO_MS = 30000;
let ID_ULTIMO_COMMENTO = 0;
let timerAggiornamento = null;

async function postJson(url, dati) {
    const risposta = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: new URLSearchParams(dati).toString(),
    });
    const json = await risposta.json().catch(() => ({ error: 'Risposta non valida dal server.' }));
    if (!risposta.ok || json.error) {
        throw new Error(json.error || `Errore HTTP ${risposta.status}`);
    }
    return json;
}

/** Messaggio sotto il form delle recensioni (al posto degli alert). */
function mostraEsito(messaggio, errore = false) {
    const form = document.getElementById('reviewForm');
    if (!form) return;
    let esito = document.getElementById('esito-recensione');
    if (!esito) {
        esito = document.createElement('p');
        esito.id = 'esito-recensione';
        esito.setAttribute('role', 'status');
        form.appendChild(esito);
    }
    esito.textContent = messaggio;
    esito.style.color = errore ? 'crimson' : '#4CAF50';
}

/** Invio di una recensione (chiamata dal pulsante "Invia Recensione"). */
async function InserisciCommento() {
    const form = document.forms['commenti'];
    const testo = form['experience'].value.trim();
    if (!testo) {
        mostraEsito('Scrivi la tua esperienza prima di inviare.', true);
        return;
    }
    if (!confirm('Vuoi pubblicare la recensione?\n\n' + testo)) return;

    try {
        const risposta = await postJson('api/salva_commento_ajax.php', {
            mondo: MONDO,
            experience: testo,
            rating: form['rating'].value,
        });
        mostraEsito(risposta.messaggio);
        updateReviewPlaceholder();
        await PrendiCommenti();
    } catch (errore) {
        mostraEsito(errore.message, true);
    }
}

/** Scarica le recensioni nuove e le aggiunge in fondo. */
async function PrendiCommenti() {
    try {
        const commenti = await postJson('api/recupera_commenti_ajax.php', { mondo: MONDO, max: ID_ULTIMO_COMMENTO });
        mostraCommenti(commenti);
    } catch (errore) {
        console.error('Errore nel recupero delle recensioni:', errore);
    }
}

function creaElemento(tag, classe, testo) {
    const el = document.createElement(tag);
    if (classe) el.className = classe;
    if (testo !== undefined) el.textContent = testo;
    return el;
}

function mostraCommenti(commenti) {
    const container = document.getElementById('reviews-container');
    if (!container) return;

    if (commenti.length === 0) {
        if (ID_ULTIMO_COMMENTO === 0) {
            container.replaceChildren(creaElemento('p', '', 'Nessuna recensione disponibile. Sii il primo a lasciare un commento!'));
        }
        return;
    }
    if (ID_ULTIMO_COMMENTO === 0) {
        container.replaceChildren(); // toglie il messaggio "nessuna recensione"
    }

    for (const c of commenti) {
        const stelle = Math.min(5, Math.max(1, Number(c.stelle) || 1));

        const intestazione = creaElemento('div', 'commento-header');
        const nome = creaElemento('span', 'username');
        nome.appendChild(creaElemento('b', '', c.username));
        const valutazione = creaElemento('span', 'rating', '⭐'.repeat(stelle) + '☆'.repeat(5 - stelle));
        valutazione.setAttribute('aria-label', `${stelle} stelle su 5`);
        intestazione.append(nome, ' ', valutazione);

        const corpo = creaElemento('div', 'commento-body');
        const paragrafo = creaElemento('p', 'testo', c.testo);
        paragrafo.style.whiteSpace = 'pre-wrap';
        corpo.appendChild(paragrafo);

        const commento = creaElemento('div', 'commento');
        commento.append(intestazione, corpo);
        container.appendChild(commento);

        ID_ULTIMO_COMMENTO = Math.max(ID_ULTIMO_COMMENTO, c.id_testo);
    }
}

/** Precompila la recensione con la località scelta (chiamata dal menu a tendina). */
function updateReviewPlaceholder() {
    const locationSelect = document.getElementById('location_selection');
    const experienceTextarea = document.getElementById('experience');
    if (locationSelect && experienceTextarea) {
        const selectedLocation = locationSelect.options[locationSelect.selectedIndex].text;
        experienceTextarea.value = `Recensione su ${selectedLocation}\n`;
    }
}

function avviaAggiornamento() {
    if (timerAggiornamento === null) {
        timerAggiornamento = setInterval(PrendiCommenti, INTERVALLO_AGGIORNAMENTO_MS);
    }
}

function fermaAggiornamento() {
    clearInterval(timerAggiornamento);
    timerAggiornamento = null;
}

document.addEventListener('DOMContentLoaded', () => {
    if (!MONDO) return;
    PrendiCommenti();
    avviaAggiornamento();
    updateReviewPlaceholder();
});

// Con la scheda in background non si interroga il server; al ritorno si aggiorna subito
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        fermaAggiornamento();
    } else if (MONDO) {
        PrendiCommenti();
        avviaAggiornamento();
    }
});
