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

function controlloEmail(string $email, mysqli $db){    
        $query_no_injection = "SELECT LOWER(email) FROM utente WHERE email = LOWER(?)";
        $stmt = mysqli_prepare($db, $query_no_injection);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        if ($result) {
             $row = mysqli_fetch_assoc($result);
            if ($row) {
             return true; //L'EMAIL E' PRESENTE
            }else {
            return false; //L'EMAIL NON E' DISPONIBILE
            }
        }else{
            $_SESSION['errore']='Impossibile contattre il database, riprova più tardi.';
            return false;
        }
}

function controlloPassword(string $email, string $pass, mysqli $db): bool{
    if(!controlloPatternPassword($pass)){
        return false;
    }
    $query_no_injection = "SELECT password FROM utente WHERE email = LOWER(?)";
    $stmt = mysqli_prepare($db, $query_no_injection);
    mysqli_stmt_bind_param($stmt, "s", $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if($result){
        $row=mysqli_fetch_assoc($result);
        if($row){
            $hash=$row['password'];

            if(password_verify($pass, $hash)){
                return true;//PASSWORD CORRETTA
            }else{
                return false; //PASSWORD ERRATA
            }
        }else {
            return false;//non esiste l'utenete???????
        }
    }else{
        $_SESSION['errore']='Impossibile contattre il database, riprova più tardi.';
        return false;
    }
}

?>