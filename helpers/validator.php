<?php

namespace helpers;

class validator
{
    /**
     * validatePOST
     * Controleer of de velden zijn ingevuld en filter de input
     *
     * @param  array $fields
     * @return void
     */
    static function validatePOST($fields)
    {
        // loop alle velden door in de array
        foreach ($fields as $field) {

            // als veld niet in opgegeven, stuur foutmelding
            if (empty($_POST[$field])) {
                throw new \Exception('Niet alle velden zijn ingevuld', 400);
            }

            // controleer of veld voldoet aan citeria
            switch ($field) {
                case 'me':
                    if (preg_match("/\W/", $_POST[$field])) {
                        throw new \Exception('Je naam mag alleen bestaan uit letters en cijfers', 400);
                    }
                    break;

                case 'partner':
                    if (preg_match("/\W/", $_POST[$field])) {
                        throw new \Exception('De naam van je partner mag alleen bestaan uit letters en cijfers', 400);
                    }
                    break;

                case 'date':
                    // splits dd/mm/yyyy naar ['dd','mm','yyyy'] ($matches)
                    if (preg_match("/([0-9]{2})\/([0-9]{2})\/([0-9]{4})/", $_POST[$field], $matches)) {
                        // controleer of het een geldige datum is
                        if (!checkdate($matches[2], $matches[1], $matches[3])) {
                            throw new \Exception('Geen geldige datum ingevuld', 400);
                        }
                    } else {
                        // datum niet in juiste format ingevoerd
                        throw new \Exception('Vul de datum alsvolgt in: dd/mm/jjjj', 400);
                    }
                    break;

                case 'invitecode':
                    if (preg_match("/\W/", $_POST[$field])) {
                        throw new \Exception('Invitecodes bestaan alleen uit letters en cijfers', 400);
                    }
                    break;

                case 'email':
                    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                        throw new \Exception('Geen geldig e-mailadres ingevuld', 400);
                    }
                    break;

                case 'linkingcode':
                    if (preg_match("/\W/", $_POST[$field])) {
                        throw new \Exception('Linkcodes bestaan alleen uit letters en cijfers', 400);
                    }
                    break;

                case 'username':
                    if (preg_match("/\W/", $_POST[$field])) {
                        throw new \Exception('Gebruikersnaam mag alleen uit letters en cijfers', 400);
                    }
                    break;

                case 'password':
                    if (preg_match("/\W/", $_POST[$field])) {
                        throw new \Exception('Wachtwoord mag alleen bestaan uit letters en cijfers', 400);
                    }
                    break;

                case 'password2':
                    if (preg_match("/\W/", $_POST[$field])) {
                        throw new \Exception('Wachtwoord mag alleen bestaan uit letters en cijfers', 400);
                    }
                    break;

                case 'sequence':
                    // sequence wordt als volgt aangeleverd: naamcadeau,positie,naamcadeau,positie etc
                    // splits string op in een array, gescheiden met komma
                    $sequence = explode(",", $_POST[$field]);

                    // loop array per set (2) af
                    for ($i = 0; $i < count($sequence); $i += 2) {
                        if (preg_match('/[^A-Za-z0-9" "\-]/', $sequence[$i])) {
                            // naam van gift bevat vreemde tekens
                            throw new \Exception('Fout in sequence string, probeer het later nogmaals', 400);
                        }
                        if (!filter_var($sequence[$i + 1], FILTER_VALIDATE_INT)) {
                            // sequence is niet een int
                            throw new \Exception('Fout in sequence string, probeer het later nogmaals', 400);
                        }
                    }
                    break;

                case 'name':
                    // verwijder vreemde tekens
                    $_POST[$field] = preg_replace('/[^A-Za-z0-9" "\-]/', '', $_POST[$field]);
                    // trim string
                    $_POST[$field] = trim($_POST[$field]);
                    break;

                case 'oldname':
                    if (preg_match('/[^A-Za-z0-9" "\-]/', $_POST[$field])) {
                        // Oude naam bevat vreemde tekens.
                        throw new \Exception('Fout in request', 400);
                    }
                    break;

                case 'summary':
                    // verwijder vreemde tekens
                    $_POST[$field] = preg_replace('/[^A-Za-z0-9" ",.:()\-]/', '', $_POST[$field]);
                    // trim string
                    $_POST[$field] = trim($_POST[$field]);
                    break;

                default:
                    throw new \Exception('Niet alle velden zijn ingevuld', 400);
            }
        }
    }
}
