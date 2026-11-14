-- Migrazione novembre 2026: testo delle recensioni salvato in chiaro.
-- Prima il server applicava htmlspecialchars() al salvataggio e il browser faceva di nuovo
-- l'escape: gli apostrofi comparivano come &#039;. Ora l'escape avviene solo in visualizzazione,
-- quindi i commenti già salvati vanno riportati in chiaro. Da eseguire una volta sola
-- (es. da phpMyAdmin su InfinityFree). &amp; va sostituito per ultimo.

UPDATE commento SET testo =
    REPLACE(
        REPLACE(REPLACE(REPLACE(REPLACE(testo, '&lt;', '<'), '&gt;', '>'), '&quot;', '"'), '&#039;', ''''),
    '&amp;', '&');
