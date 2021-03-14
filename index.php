<?php

session_start();
$con = mysqli_connect("localhost", "dugiwarc", "069249335", "budget_app");

if (mysqli_connect_errno()) {
    echo "Failed to connect" . mysqli_connect_errno();
}

// Queries
$type_paiements_query = mysqli_query($con, "SELECT idMoyenPaiement, moyenPaiement FROM moyenpaiements");
$type_paiements_query_filter = mysqli_query($con, "SELECT idMoyenPaiement, moyenPaiement FROM moyenpaiements");
$categorie_query = mysqli_query($con, "SELECT idCategories, nomCategorie FROM categories");
$categorie_query_filter = mysqli_query($con, "SELECT idCategories, nomCategorie FROM categories");
$transactions_query = mysqli_query($con, "SELECT dataTransaction, montant, idCategories, idMoyenPaiement FROM transactions");
$transactions_query_last_5 = mysqli_query($con, "SELECT idTransactions, dataTransaction, montant, transactions.idCategories, categories.crdb, idMoyenPaiement FROM transactions INNER JOIN categories WHERE categories.idCategories = transactions.idCategories ORDER BY idTransactions DESC LIMIT 5");

// Lambdas
function getCategoryById($id)
{
    $con = mysqli_connect("localhost", "dugiwarc", "069249335", "budget_app");
    $cats_array_query = mysqli_query($con, "SELECT nomCategorie FROM categories WHERE idCategories = $id");
    $cats_array = mysqli_fetch_array($cats_array_query);
    return $cats_array[0];
};

function getDebit()
{
    $con = mysqli_connect("localhost", "dugiwarc", "069249335", "budget_app");
    $debit_query = mysqli_query($con, "SELECT SUM(montant) AS total FROM ( SELECT montant, categories.crdb FROM transactions INNER JOIN categories WHERE transactions.idCategories = categories.idCategories) AS sum_credit WHERE sum_credit.crdb = -1");
    $debit_total = mysqli_fetch_array($debit_query);
    return $debit_total[0];
}

function getCredit()
{
    $con = mysqli_connect("localhost", "dugiwarc", "069249335", "budget_app");
    $credit_query = mysqli_query(
        $con,
        "SELECT SUM(montant) AS total FROM ( SELECT montant, categories.crdb FROM transactions INNER JOIN categories WHERE transactions.idCategories = categories.idCategories) AS sum_credit WHERE sum_credit.crdb = 1"
    );

    $credit_total = mysqli_fetch_array($credit_query);
    return $credit_total[0];
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
        <form action="index.php" method="POST">
            <h2>
                Ajouter transaction
            </h2>
            <div>
                <label for="montant">Montant</label>
                <input type="number" min="0" step="0.01" name="montant" id="montant" />
            </div>
            <div>
                <label for=" categorie">Categorie</label>
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

                <h3>Balance</h3>
                <h2><?php getBalance() ?></h2>
            </section>
            <button id="display-form">
                Add transaction
            </button>
        </section>
        <section class="chart">
            <section>
                <div class="title">Presentation</div>
                <button>Mensuel</button>
                <button>Annuel</button>
            </section>
            <canvas id="myChart" width="100" height="100"></canvas>
        </section>
    </main>

    <!-- Transaction -->
    <section class="transactions">
        <h2>Transactions recentes</h2>
        <section class="filter-transactions">
            <div class="title">Filtrer transactions</div>
            <select value="0" name="" id="">
                <option value="0">Mensuel</option>
                <option value="1">Annuel</option>
            </select>
            <select value="0" name="" id="">
                <option value=""> -- Categories --</option>
                <?php
                while ($row = mysqli_fetch_array($categorie_query_filter)) {
                    echo '<option value="' . $row['idCategories'] . '">' . $row['nomCategorie'] . '</option>';
                }
                ?>
            </select>
            <select id="moyen" name="moyen">
                <option value=""> -- Moyens --</option>
                <?php
                while ($row = mysqli_fetch_array($type_paiements_query_filter)) {
                    echo '<option value="' . $row['idMoyenPaiement'] . '">' . $row['moyenPaiement'] . '</option>';
                }
                ?>
            </select>

        </section>
        <section class="list-transactions">
            <?php
            while ($row = mysqli_fetch_array($transactions_query_last_5)) {
                echo '<section class="transaction">';
                $index = (int)$row['idCategories'];
                echo '<div class="title">' . getCategoryById($index) . '</div>';
                echo '<div class="date">' . array_shift(explode(" ", $row['dataTransaction'])) . '</div>';
                if ($row['crdb'] == 1) {
                    echo
                    '<div class="amount" id="credit">' . $row['montant'] . '</div>';
                } else {
                    echo '<div class="amount" id="debit">' . $row['montant'] . '</div>';
                }
                echo '</section>';
            }
            ?>
        </section>
    </section>

    <script src='./main.js'></script>
    <script>
        var ctx = document.getElementById("myChart");

        var myChart = new Chart(ctx, {
            type: "pie",
            data: {
                labels: ["Debit", "Credit"],
                datasets: [{
                    // label: "# of Votes",
                    data: [<?php echo getDebit() ?>, <?php echo getCredit() ?>],
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