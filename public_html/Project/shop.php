<?php
require(__DIR__ . "/../../partials/nav.php");
$PER_PAGE = 10;
$results = [];
$itemCategory = "";
$itemName = "";
$sortPrice = false;
$current_page = 0;
if(isset($_GET["itemCategory"])){
    $itemCategory = $_GET["itemCategory"];
}
if(isset($_GET["itemName"])){
    $itemName = $_GET["itemName"];
}
if(isset($_GET["sortPrice"])){
    $sortPrice = true;
}
if(isset($_POST["submit"])){
    if(isset($_POST["itemName"])){
        $itemName = $_POST["itemName"];
    }
    if(isset($_POST["itemCategory"])){
        $itemCategory = $_POST["itemCategory"];
    }
    if(isset($_POST["sortPrice"])){
        $sortPrice = true;
    }
}
$params = [];
$sqlstr = "SELECT * FROM Products WHERE 1=1 AND stock > 0 AND NOT visibility = 'false' ";
if(!empty($itemCategory)){
    $sqlstr = $sqlstr . " AND category = :itemCategory";
    $params[":itemCategory"] = $itemCategory;
}
if(!empty($itemName)){
    $sqlstr = $sqlstr . " AND name like :itemName";
    $params[":itemName"] = "%" . $itemName . "%";
}
if(isset($_POST['sortPrice'])){
    $sqlstr = $sqlstr . " ORDER BY cost";
}
$sqlstr .= " LIMIT " . $current_page * $PER_PAGE . ","  . $PER_PAGE;
$count_str = "SELECT COUNT(*) FROM " . explode('LIMIT', explode('FROM', $sqlstr)[1])[0]; //Circumcise the sql string in order to obtain count
$db = getDB();
$stmt = $db->prepare($sqlstr);
try {
    $stmt->execute($params);
    $r = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if ($r) {
        $results = $r;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
}
$stmt = $db->prepare($count_str);
try {
    $stmt->execute($params);
    $r = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($r) {
        $count_results = $r;
    }
} catch (PDOException $e) {
    flash("<pre>" . var_export($e, true) . "</pre>");
}
?>
<img class='cactus' src="<?php echo(__DIR__ . '/../../../Project/cacti/Kaktus-%C3%A4ndrad.png');?>"/>
<div class="page_traverser">
<?php
if($current_page >= 1){
    echo("<a class='paginate_button' href = shop.php?current_page=" . $current_page-1 . "&itemName=" . $itemName . "&itemCategory=" . $itemCategory . ">Previous</a>");
}
if(($current_page+1)*$PER_PAGE < $count_results["COUNT(*)"]){
    echo("<a class='paginate_button' href = shop.php?current_page=" . $current_page+1 . "&itemName=" . $itemName . "&itemCategory=" . $itemCategory. ">Next</a>");
}
echo("</div>");
?>
<div class="container-fluid">
    <h1>Shop</h1>
    <form method="POST">
        <div>
            <input type="search" name="itemName" placeholder="Item Filter" /><br>
            <input type="search" name="itemCategory" placeholder="Category Filter" /><br>
            <input type="checkbox" name="sortPrice" value="Sort by price"/> Sort by price<br>
            <input type="submit" value="Search" />
        </div>
    </form>
    <?php
    $flopper=0;
    echo("<div class='row'>");
    foreach ($results as $index => $record){
        $flopper++;
        $card = <<<GODAN
        <div class='info_card'>
        <h2>{$record["name"]}</h2><br><br>
        <h3>Type: {$record["category"]}</h3><br>
        <h3>Cost: {$record["cost"]}</h3><br>
        <a href='item_info.php?id={$record["id"]}'>Item info</a><br>
        <a href='add_to_cart.php?id={$record["id"]}'>Add to cart</a><br>
        GODAN;
        if(has_role("Admin")){
            $card .= "<a href='admin/edit_item.php?id={$record["id"]}'>Edit</a>";
        }
        $card .= "</div>";
        if($flopper == 2){
            $card .= "</div><div class='row'>";
            $flopper = 0;
        }
        echo($card);
    }
    ?>
</div>
<?php
require_once(__DIR__ . "/../../partials/flash.php");
?>