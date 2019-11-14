<?php
include '../Conf/PersistentManager.php';
include '../Utils/SessionUtils.php';


if (isset($_POST["emailLogin"])) {
    checkAction();
}
if (isset($_POST["emailSignup"])) {

    createAction();

}

function checkAction()
{
    $dbh = connect();
    if(check($_POST["emailLogin"], $_POST["passwordLogin"], $dbh))
     {
     // Establecemos la sesión
         startSessionIfNotStarted();
         setSession($_POST["emailLogin"]);

         header('Location: ../../index.php');
     }
     else
    {
        header('Location: ../../index2.php');
    }
}

// Función encargada de crear nuevos usuarios
function createAction()
{
    $dbh = connect();
    $user = array(
        "nombre" => $_POST["name"],
        "apellido" => $_POST["lastname"],
        "telefono" => $_POST["phone"],
        "fecha" => $_POST["birthdate"],
        "genero" => $_POST["gender"],
        "profImg" => $_POST["profImg"],
        "email" => $_POST["emailSignup"],
        "password" => $_POST["passwordSignup"]
        );


        insert($user, $dbh);


    // Establecemos la sesión
    startSessionIfNotStarted();
    setSession($_POST["emailSignup"]);



    header('Location: ../../index.php');
}


//Comprueba si los datos ya existen en la base de datos
function check($email, $password, $dbh)
{

    $data = array(
        'email' => $email,
        'password' => $password
    );

    try {
        $stmt = $dbh->prepare("SELECT usuario FROM usuarios WHERE usuario = :email AND contrasena = :password");

        $stmt->setFetchMode(PDO::FETCH_OBJ);
        $stmt->execute($data);
        $num = 0;
        while($row = $stmt->fetch()){
            $num++;
        }
        if ($num > 0) {
            return true;
        } else {
            return false;
        }
    }
    catch (PDOException $e){
        die($e->getMessage());
    }

}

//Añade en la tabla seleccionada todos sus datos
function insert($user, $dbh)
{
    $data2 = array(
        "email" => $user["email"],
        "password" => $user["password"]
    );
    try{
        $stmt = $dbh->prepare("INSERT INTO usuarios(usuario, contrasena) VALUES (:email, :password)");
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        $stmt->execute($data2);
    }catch (PDOException $e){
        die($e->getMessage());
    }

    $value = getIdUsuario($dbh, $data2);

    $data = array(
        'nombre' => $user["nombre"],
        'apellidos' => $user["apellido"],
        'telefono' => $user["telefono"],
        'fechaNacimiento' => $user["fecha"],
        'genero' => $user["genero"],
        'email' => $user["email"],
        'id_usuario' => $value,
        'profImage' => $user["profImg"]
    );

    try{
        $stmt2 = $dbh->prepare("INSERT INTO perfiles(nombre, apellido, correo, sexo, telefono, fechaNacimiento, id_usuario, foto) VALUES (:nombre, :apellidos, :email, :genero, :telefono, :fechaNacimiento, :id_usuario ,:profImage)");
        $stmt2->setFetchMode(PDO::FETCH_OBJ);
        $stmt2->execute($data);
    }catch (PDOException $e){
        die($e->getMessage());
    }
}
function getIdUsuario($dbh, $data){
    try{
        $stmt = $dbh->prepare("SELECT id FROM usuarios WHERE usuario=:email AND contrasena=:password");
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        $stmt->execute($data);
        while($row = $stmt->fetch()){
            $value = $row->id;
        }
    }catch (PDOException $e){
        die($e->getMessage());
    }
    return $value;
}


function getNamebyIDEmail(){
    $dbh = connect();
    $email = getSession();
    $data = array(
      'email' => $email
    );
    $stmt = $dbh->prepare("SELECT nombre FROM perfiles WHERE correo=:email");
    $stmt->setFetchMode(PDO::FETCH_OBJ);
    $stmt->execute($data);
    while($row = $stmt->fetch()){
        $name = $row->nombre;
    }

    return $name;
}
function getImgProfile(){
    $dbh = connect();
    $email = getSession();
    $data = array(
      'email' => $email
    );
    try{
        $stmt = $dbh->prepare("SELECT foto FROM perfiles WHERE correo=:email");
        $stmt->setFetchMode(PDO::FETCH_OBJ);
        $stmt->execute($data);
        while($row = $stmt->fetch()){
            $value = $row->id;
        }
    }catch (PDOException $e){
        die($e->getMessage());
    }
    return $value;
}
?>
