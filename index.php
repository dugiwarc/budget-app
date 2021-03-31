<?php
session_start();
$con = mysqli_connect("mysql-georgebotnaru.alwaysdata.net", "214676_test", "069249335", "georgebotnaru_budget_app");
if (mysqli_connect_errno()) {
    echo "Failed to connect" . mysqli_connect_errno();
}

if (isset($_POST['Filtrer'])) {
    $_SESSION['f_cats'] = $_POST['f_cats'];
    $_SESSION['f_moyen'] = $_POST['f_moyen'];
    $_SESSION['f_crdb'] = $_POST['f_crdb'];
}

if (isset($_POST['mensuel'])) {
    $_SESSION['mode'] = 'Mensuel';
} else if (isset($_POST['annuel'])) {
    $_SESSION['mode'] = 'Annuel';
}

if (!isset($_SESSION['mode'])) {
    $_SESSION['mode'] = 'Annuel';
}

if (!isset($_SESSION['year'])) {
    $_SESSION['year'] = 2021;
}

if (!isset($_SESSION['month'])) {
    $_SESSION['month'] = 3;
}


if (isset($_POST['register-presentation'])) {
    if ($_POST['year'])
        $_SESSION['year'] = $_POST['year'];
    if ($_POST['month'])
        $_SESSION['month'] = $_POST['month'];
}

// Queries
$type_paiements_query = mysqli_query($con, "SELECT idMoyenPaiement, moyenPaiement FROM moyenpaiements");
$type_crdb_query = mysqli_query($con, "SELECT DISTINCT(crdb) FROM categories");
$type_paiements_query_filter = mysqli_query($con, "SELECT idMoyenPaiement, moyenPaiement FROM moyenpaiements");
$categorie_query = mysqli_query($con, "SELECT idCategories, nomCategorie FROM categories");
$categorie_query_filter = mysqli_query($con, "SELECT idCategories, nomCategorie FROM categories");
$transactions_query = mysqli_query($con, "SELECT dataTransaction, montant, idCategories, idMoyenPaiement FROM transactions");

// Lambdas
function getCategoryById($id)
{
    $con = mysqli_connect("mysql-georgebotnaru.alwaysdata.net", "214676_test", "069249335", "georgebotnaru_budget_app");
    $cats_array_query = mysqli_query($con, "SELECT nomCategorie FROM categories WHERE idCategories = $id");
    $cats_array = mysqli_fetch_array($cats_array_query);
    return $cats_array[0];
};

function getDebitMonthly()
{
    $month = $_SESSION['month'];
    $con = mysqli_connect("mysql-georgebotnaru.alwaysdata.net", "214676_test", "069249335", "georgebotnaru_budget_app");
    $debit_query = mysqli_query($con, "SELECT SUM(montant) AS total FROM ( SELECT montant, categories.crdb FROM transactions INNER JOIN categories WHERE transactions.idCategories = categories.idCategories AND MONTH(dataTransaction) = $month) AS sum_credit WHERE sum_credit.crdb = -1");
    $debit_total = mysqli_fetch_array($debit_query);
    return $debit_total[0];
}

function getCreditMonthly()
{
    $month = $_SESSION['month'];
    $con = mysqli_connect("mysql-georgebotnaru.alwaysdata.net", "214676_test", "069249335", "georgebotnaru_budget_app");
    $credit_query = mysqli_query(
        $con,
        "SELECT SUM(montant) AS total FROM ( SELECT montant, dataTransaction, categories.crdb FROM transactions INNER JOIN categories WHERE transactions.idCategories = categories.idCategories AND MONTH(dataTransaction) = $month) AS sum_credit WHERE sum_credit.crdb = 1"
    );

    $credit_total = mysqli_fetch_array($credit_query);
    return $credit_total[0];
}


function getDebitYearly()
{
    $year = $_SESSION['year'];
    $con = mysqli_connect("mysql-georgebotnaru.alwaysdata.net", "214676_test", "069249335", "georgebotnaru_budget_app");
    $debit_query = mysqli_query($con, "SELECT SUM(montant) AS total FROM ( SELECT montant, categories.crdb FROM transactions INNER JOIN categories WHERE transactions.idCategories = categories.idCategories AND YEAR(dataTransaction) = $year) AS sum_credit WHERE sum_credit.crdb = -1");

    $debit_total = mysqli_fetch_array($debit_query);

    return $debit_total[0];
}

function getCreditYearly()
{
    $year = $_SESSION['year'];
    $con = mysqli_connect("mysql-georgebotnaru.alwaysdata.net", "214676_test", "069249335", "georgebotnaru_budget_app");
    $credit_query = mysqli_query(
        $con,
        "SELECT SUM(montant) AS total FROM ( SELECT montant, dataTransaction, categories.crdb FROM transactions INNER JOIN categories WHERE transactions.idCategories = categories.idCategories AND YEAR(dataTransaction) = $year) AS sum_credit WHERE sum_credit.crdb = 1"
    );

    $credit_total = mysqli_fetch_array($credit_query);

    return $credit_total[0];
}

function getOptions()
{
    $id_cat = $_SESSION['f_cats'];
    $id_moyen = $_SESSION['f_moyen'];
    $id_crdb = $_SESSION['f_crdb'];

    $con = mysqli_connect("mysql-georgebotnaru.alwaysdata.net", "214676_test", "069249335", "georgebotnaru_budget_app");

    if ($id_cat > 0 && $id_moyen > 0 && ($id_crdb == 1 || $id_crdb == -1)) {
        $transactions_query_last_5 = mysqli_query($con, "SELECT * FROM (SELECT idTransactions, dataTransaction, montant, transactions.idCategories, categories.crdb, idMoyenPaiement FROM transactions INNER JOIN categories WHERE categories.idCategories =
transactions.idCategories) as a INNER JOIN moyenpaiements where a.idMoyenPaiement = moyenpaiements.idMoyenPaiement AND a.idMoyenPaiement = $id_moyen AND a.idCategories = $id_cat AND crdb = $id_crdb ORDER BY dataTransaction DESC");
    } else if (
        $id_cat > 0 && $id_moyen > 0
    ) {
        $transactions_query_last_5 = mysqli_query($con, "SELECT * FROM (SELECT idTransactions, dataTransaction, montant, transactions.idCategories, categories.crdb, idMoyenPaiement FROM transactions INNER JOIN categories WHERE categories.idCategories =
transactions.idCategories) as a INNER JOIN moyenpaiements where a.idMoyenPaiement = moyenpaiements.idMoyenPaiement AND a.idMoyenPaiement = $id_moyen AND a.idCategories = $id_cat  ORDER BY dataTransaction DESC");
    } else if ($id_cat > 0) {
        $transactions_query_last_5 = mysqli_query($con, "SELECT * FROM (SELECT idTransactions, dataTransaction, montant, transactions.idCategories, categories.crdb, idMoyenPaiement FROM transactions INNER JOIN categories WHERE categories.idCategories =
transactions.idCategories) as a INNER JOIN moyenpaiements where a.idMoyenPaiement = moyenpaiements.idMoyenPaiement AND a.idCategories = $id_cat ORDER BY dataTransaction DESC");
    } else if ($id_moyen > 0) {
        $transactions_query_last_5 = mysqli_query($con, "SELECT * FROM (SELECT idTransactions, dataTransaction, montant, transactions.idCategories, categories.crdb, idMoyenPaiement FROM transactions INNER JOIN categories WHERE categories.idCategories =
transactions.idCategories) as a INNER JOIN moyenpaiements where a.idMoyenPaiement = moyenpaiements.idMoyenPaiement AND a.idMoyenPaiement = $id_moyen ORDER BY dataTransaction DESC");
    } else if ($id_crdb == 1 || $id_crdb == -1) {
        $transactions_query_last_5 = mysqli_query($con, "SELECT * FROM (SELECT idTransactions, dataTransaction, montant, transactions.idCategories, categories.crdb, idMoyenPaiement FROM transactions INNER JOIN categories WHERE categories.idCategories =
transactions.idCategories) as a INNER JOIN moyenpaiements where a.idMoyenPaiement = moyenpaiements.idMoyenPaiement AND a.crdb= $id_crdb");
    } else {
        $transactions_query_last_5 = mysqli_query($con, "SELECT * FROM (SELECT idTransactions, dataTransaction, montant, transactions.idCategories, categories.crdb, idMoyenPaiement FROM transactions INNER JOIN categories WHERE categories.idCategories = transactions.idCategories) as a INNER JOIN moyenpaiements where a.idMoyenPaiement = moyenpaiements.idMoyenPaiement ORDER BY dataTransaction DESC");
    }

    while ($row = mysqli_fetch_array($transactions_query_last_5)) {
        echo '<section class="transaction">';
        $index = (int)$row['idCategories'];
        echo '<div class="title">' . getCategoryById($index) . '</div>';
        echo '<div class="moyen">' . $row['moyenPaiement'] . '</div>';
        echo '<div class="date">' . array_shift(explode(" ", $row['dataTransaction'])) . '</div>';
        if ($row['crdb'] == 1) {
            echo
            '<div class="amount" id="credit">' . $row['montant'] . '</div>';
        } else {
            echo '<div class="amount" id="debit">' . $row['montant'] . '</div>';
        }
        echo '</section>';
    }
}


function getCredit()
{
    $con = mysqli_connect("mysql-georgebotnaru.alwaysdata.net", "214676_test", "069249335", "georgebotnaru_budget_app");
    $debit_query = mysqli_query($con, "SELECT SUM(montant) AS total FROM ( SELECT montant, categories.crdb FROM transactions INNER JOIN categories WHERE transactions.idCategories = categories.idCategories) AS sum_credit WHERE sum_credit.crdb = 1");
    $debit_total = mysqli_fetch_array($debit_query);
    return $debit_total[0];
}

function getDebit()
{
    $con = mysqli_connect("mysql-georgebotnaru.alwaysdata.net", "214676_test", "069249335", "georgebotnaru_budget_app");
    $debit_query = mysqli_query($con, "SELECT SUM(montant) AS total FROM ( SELECT montant, categories.crdb FROM transactions INNER JOIN categories WHERE transactions.idCategories = categories.idCategories) AS sum_credit WHERE sum_credit.crdb = -1");
    $debit_total = mysqli_fetch_array($debit_query);
    return $debit_total[0];
}

function getBalance()
{
    echo number_format(getCredit() - getDebit(), 2);
}


// Errors
$error_array = array();

// Form registration
if (isset($_POST['register-button'])) {
    $reg_montant = strip_tags($_POST["montant"]);
    $reg_montant = str_replace(" ", '', $reg_montant);

    $reg_cat = strip_tags($_POST['categorie']);
    $reg_cat = ucfirst(strtolower($reg_cat));

    $reg_moyen = strip_tags($_POST['moyen']);
    $reg_moyen = str_replace(' ', '', $reg_moyen);
    $reg_moyen = ucfirst(strtolower($reg_moyen));

    $reg_commentaire = strip_tags($_POST['commentaire']);
    $reg_commentaire = ucfirst(strtolower($reg_commentaire));


    if (strlen($reg_commentaire) === 0) {
        array_push($error_array, "Field commentaire cannot be empty<br/>");
    }

    if (strlen($reg_montant) === 0) {
        array_push($error_array, "Field montant cannot be empty<br/>");
    }

    if (strlen($reg_cat) === 0) {
        array_push($error_array, "Field categorie cannot be empty<br/>");
    }

    if (strlen($reg_moyen) === 0) {
        array_push($error_array, "Field moyen paiement cannot be empty<br/>");
    }



    if (empty($error_array)) {
        $insert_transaction_query = mysqli_query($con, "INSERT INTO transactions (montant, commentaire, idCategories, idMoyenPaiement) VALUES ('$reg_montant','$reg_commentaire','$reg_cat','$reg_moyen')");
        array_push($error_array, "Field moyen paiement cannot be empty<br/>");
        $reg_montant = "";
        $reg_commentaire = "";
        $reg_cat = "";
        $reg_moyen = "";
    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.min.js" integrity="sha512-d9xgZrVZpmmQlfonhQUvTR7lMPtO7NkZMkA0ABN3PHCbKA5nqylQ/yWlFAyY6hYgdF1Qh6nYiuADWwKB4C2WSw==" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="./style.css">
    <title>Budget App</title>
</head>

<body>
    <!-- Cloak -->
    <div id="cloak">
    </div>
    <!-- Form -->
    <section class="form">
        <form action="index.php" method="POST" id="modal-form">
            <h2>
                Ajouter transaction
            </h2>
            <div>
                <label for="montant">Montant</label>
                <input type="number" min="0" step="0.01" name="montant" id="montant" />
            </div>
            <div>
                <label for="categorie">Categorie</label>
                <select id="categorie" name="categorie">
                    <option value=""> -- Choisir categorie --</option>
                    <?php
                    while ($row = mysqli_fetch_array($categorie_query)) {
                        echo '<option value="' . $row['idCategories'] . '">' . $row['nomCategorie'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="moyen">Moyen de paiement</label>
                <select id="moyen" name="moyen">
                    <option value=""> -- Choisir moyen --</option>
                    <?php
                    while ($row = mysqli_fetch_array($type_paiements_query)) {
                        echo '<option value="' . $row['idMoyenPaiement'] . '">' . $row['moyenPaiement'] . '</option>';
                    }
                    ?>
                </select>
            </div>
            <div>
                <label for="commentaire">Commentaire</label>
                <textarea id="commentaire" cols="30" rows="10" name="commentaire"></textarea>
            </div>
            <div class="buttons">
                <button id="cancel-form">
                    Annuler
                </button>
                <input type="submit" id="register-form" name="register-button" value="Enregistrer">
            </div>
        </form>
    </section>

    <!-- Navigation -->
    <nav>
        <section class="logo">
            <img src="./assets/budget.png" alt="logo">
            <div class="text">Budget app</div>
        </section>
    </nav>

    <!-- Main -->
    <main>
        <section class="panels">
            <section class="balance">

                <h3>Solde</h3>
                <h2><?php getBalance() ?></h2>
            </section>
            <button id="display-form">
                Ajouter transaction
            </button>
        </section>

        <!-- Chart -->
        <section class="chart">
            <section id="info">
                <form id="info-mode" action="index.php" method="POST">
                    <?php if ($_SESSION['mode'] === 'Mensuel') {
                        echo
                        '<div>
                        <label for="month">Mois</label>
                        <input type="number" id="month" value=' . $_SESSION['month'] . ' name="month" min="0" max="12">
                    </div>';
                    } else {
                        echo '<div>
                        <label for="year">Annee</label>
                        <input type="number" id="year" value=' . $_SESSION['year'] . ' name="year" min="2000" max="2032">
                    </div>';
                    } ?>
                    <input type="submit" value="Filtrer" name="register-presentation" id="register-presentation">
                </form>
            </section>
            <section id="nav">
                <form action="index.php" method="POST">
                    <div class="title">Presentation</div>
                    <input type="submit" name="mensuel" id="mensuel" value="Mensuel" <?php if ($_SESSION['mode'] == 'Mensuel') {
                                                                                            echo 'style="border: 4px solid darkgrey;"';
                                                                                        } ?> />
                    <input type="submit" name="annuel" id="annuel" value="Annuel" <?php if ($_SESSION['mode'] == 'Annuel') {
                                                                                        echo 'style="border: 4px solid darkgrey;"';
                                                                                    } ?> />
                </form>
            </section>
            <canvas id="myChart" width="100" height="100"></canvas>
        </section>
    </main>

    <!-- Transaction -->
    <section class="transactions">
        <h2>Transactions</h2>
        <section class="filter-transactions">
            <form action="index.php" method="POST">
                <input type="submit" class="filter_button" name="Filtrer" value="Filtrer">
                <select name="f_cats" id="f_cats">
                    <option value=""> -- Toutes catégories --</option>
                    <?php
                    while ($row = mysqli_fetch_array($categorie_query_filter)) {
                        if ($row['idCategories'] == $_SESSION["f_cats"])
                            echo
                            '<option selected="selected" value="' . $row['idCategories'] . '">' . $row['nomCategorie'] . '</option>';
                        else
                            echo '<option value="' . $row['idCategories'] . '">' . $row['nomCategorie'] . '</option>';
                    }
                    ?>
                </select>
                <select id="f_crdb" name="f_crdb">
                    <option value=""> -- Tout type --</option>
                    <?php
                    while ($row = mysqli_fetch_array($type_crdb_query)) {
                        if ($row['crdb'] == $_SESSION['f_crdb'])
                            echo '<option selected="selected" value="' . $row['crdb'] . '">' . $row['crdb'] . '</option>';
                        else
                            echo '<option value="' . $row['crdb'] . '">' . $row['crdb'] . '</option>';
                    }
                    ?>
                </select>
                <select id="f_moyen" name="f_moyen">
                    <option value=""> -- Toutes moyennes --</option>
                    <?php
                    while ($row = mysqli_fetch_array($type_paiements_query_filter)) {
                        if ($row['idMoyenPaiement'] == $_SESSION['f_moyen'])
                            echo '<option selected="selected" value="' . $row['idMoyenPaiement'] . '">' . $row['moyenPaiement'] . '</option>';
                        else
                            echo '<option value="' . $row['idMoyenPaiement'] . '">' . $row['moyenPaiement'] . '</option>';
                    }
                    ?>
                </select>
            </form>

        </section>
        <section class="list-transactions">
            <?php
            getOptions()
            ?>
        </section>
    </section>

    <script src='./main.js'></script>
    <script>
        let defaultChartView = "monthly";

        var ctx = document.getElementById("myChart");
        const monthButton = document.querySelector("#mensuel");
        const annualButton = document.querySelector("#annuel");
        const registerPresentation = document.querySelector("#register-presentation");

        var myChart = new Chart(ctx, {
            type: "pie",
            data: {
                labels: ["Debit", "Credit"],
                datasets: [{
                    // label: "# of Votes",
                    data: [<?php if ($_SESSION['mode'] === 'Mensuel') {
                                echo getDebitMonthly();
                            } else {
                                echo getDebitYearly();
                            } ?>, <?php if ($_SESSION['mode'] === 'Annuel') {
                                        echo getCreditYearly();
                                    } else {
                                        echo getCreditMonthly();
                                    } ?>],
                    backgroundColor: [
                        "#f7c662",
                        "#ef5260",
                    ],
                }, ],
            },
            options: {},
        });
    </script>
</body>

</html>