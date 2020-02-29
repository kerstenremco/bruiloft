<?php
    require_once 'vendor/autoload.php';
    require_once 'database.php';
    require_once 'objects/user.php';
    $db = new Database('localhost', 'bruiden', 'root', 'rootroot');
    $conn = $db->conn;
    $loader = new \Twig\Loader\FilesystemLoader('views');
    $twig = new \Twig\Environment($loader);

    if(!isset($_COOKIE["PHPSESSID"]))
    {
        // er is geen sessie cookie meegestuurd, laat inlogpagina zien
        echo $twig->render('login.twig', ['name' => 'Fabien']);
    } else {
        // inlogsessie ontvangen
        session_start();
        if(!empty($_SESSION)) {
            // sessie bekend, laat pagina zien
            $user = $_SESSION['user'];
            $user->updateWedding();
            if($user->wedding == null) {
                // nog geen bruiloft aangemaakt
                echo $twig->render('base.twig', [
                    'naam' => $user->gebruikersnaam,
                    'action' => 'create'
                ]);
            } else {
                // bruiloft bekend, laat bewerkpagina zien
                $action;
                if(isset($_GET['action']) && $_GET['action'] == 'bewerken') $action = 'edit';
                elseif(isset($_GET['action']) && $_GET['action'] == 'uitnodigen') $action = 'invite';
                else $action = 'home';
                echo $twig->render('base.twig', [
                    'naam' => $user->gebruikersnaam,
                    'hasWedding' => true,
                    'person1' => $user->wedding->person1,
                    'person2' => $user->wedding->person2,
                    'date' => $user->wedding->date,
                    'action' => $action
                ]);
            }
        } else {
            // sessie niet geldig, laat inlogpagina zien
            echo $twig->render('login.twig', ['name' => 'Fabien']);
        }
    };
    

?>