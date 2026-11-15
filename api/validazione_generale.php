<?php


function controlloPatternPassword(string $pass){
    $pattern="/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&_])[A-Za-z\d@$!%*?&_]{8,}$/";
    return preg_match($pattern, $pass);
    
}
function controlloPatternEmail(string $email){
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}
function controlloPatternNome(string $nome){
    $pattern = "/^[a-zA-Z][a-zA-Z0-9]*$/";
    return preg_match($pattern,$nome);
}
function controlloPatternCognome(string $cognome){
    $pattern ="/^[a-zA-Z]+$/";
    return preg_match($pattern,$cognome);
}

// Le verifiche di email e password sul database sono in api/elabora_autenticazione.php
