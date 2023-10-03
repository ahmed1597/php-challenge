//1) Please, fully explain this function: document iterations, conditionals, and the function objective as a whole
<?php
function($p, $o, $ext) {
    $items = [];
    $sp = false;
    $cd = false;

    $ext_p = [];

    foreach ($ext as $i => $e) {
      $ext_p[$e['price']['id']] = $e['qty'];
    }

    foreach ($o['items']['data'] as $i => $item) {
      $product = [
        'id': $item['id']
      ];

      if isset($ext_p[$item['price']['id']]) {
          $qty = $ext_p[$item['price']['id']];
          if ($qty < 1) {
              $product['deleted'] = true;
          } else {
              $product['qty'] = $qty;
          }
          unset($ext_p[$item['price']['id']]);
      } else if ($item['price']['id'] == $p['id']) {
          $sp = true;
      } else {
          $product['deleted'] = true
          $cd = true
      }
      
      $items[] = $product;
    }
    
    if (!$sp) {
      $items[] = [
        'id': $p['id'],
        'qty': 1
      ];
    }
    
    foreach ($ext_p as $i => $details) {
      if ($details['qty'] < 1) {
          continue;
      }

      $items[] = [
        'id': $details['price'],
        'qty': $details['qty']
      ];
    }
    
    return $items;
?>
This function seems to be part of code that processes order items.
1.Function Declaration:
  The function is declared with three parameters: $p, $o, & $ext.
2.Initialization:
  $items is initialized as an empty array. This array will be used to store processed product items.
  $sp ("selected product") & $cd ("custom deleted") are initialized as false. These variables are used to keep track of certain conditions during processing.
  $ext_p is initialized as an empty array. It will be used to store extended product details as per the provided $ext array.
3.Processing Extended Product Details:
  The first foreach loop iterates through the $ext array and populates the $ext_p array with quantities of each product based on their price ID.
4.Processing Order Items:
  The second foreach loop iterates through the items in the order ($o['items']['data']).
  For each item, a new $product array is created with the 'id' field set to the item's ID.
5.Conditional checks are performed:
  If the item's price ID exists in $ext_p, the quantity is set from $ext_p. If the quantity is less than 1, a 'deleted' flag is set to true, indicating that this item is deleted from the order. Otherwise, the 'qty' field is set to the quantity.
  If the item's price ID matches the selected product's ID ($p['id']), the $sp flag is set to true. This indicates that the selected product is found in the order.
  If none of the above conditions match, the 'deleted' flag is set to true, indicating that this item is deleted, and the $cd flag is set to true, indicating that a custom deletion has occurred.
  The processed product is added to the $items array.
6.Adding Selected Product If Missing:
  After processing all order items, there is a conditional check to see if the selected product ($p) is found in the order. If it's not found ($sp is still false), a new product with a quantity of 1 is added to the $items array.
7.Processing Remaining Extended Product Details:
  Another foreach loop iterates through the remaining entries in $ext_p.
  If the quantity is less than 1, the loop continues to the next entry.
  For each remaining entry, a new product is added to the $items array with the 'id' set to the price ID and 'qty' set to the quantity.
8.Return:
  Finally, the function returns the $items array, which contains processed product items.
9.Overall Objective:
  The function processes order items ($o) and extended product details ($ext) and produces a modified list of product items in the $items array. It handles scenarios where items can be deleted or custom deleted, ensures that the selected product is in the list if it wasn't initially, and includes any remaining extended product details. The function's goal is to prepare a list of products that represent the final state of the order after processing.


2) Write a class "LetterCounter" and implement a static method "CountLettersAsString" which receives a string parameter and returns a string that shows how many times each letter shows up in the string by using an asterisk (*).
Example: "Interview" -> "i:**,n:*,t:*,e:**,r:*,v:*,w:*"
<?php 
class LetterCounter {
  public static function CountLettersAsString($word) {
    $freq = array();
    for ($i=0; $i<strlen($word); $i++) {
      $letter = strtolower($word[$i]);
      $freq[$letter] = array_key_exists($letter, $freq) ? $freq[$letter].'*' : $letter.':*';
    }
    return count($freq) ? implode(",",$freq) : "";
  }
}
$CountLettersAsString = LetterCounter::CountLettersAsString("Interview");
echo($CountLettersAsString);
?>

<?php 
  #This will be used for Task 3 & 4 
  class APIService {
    public static function GetData($URL){
      return @file_get_contents($URL);
    }
  }
?>
3) Write a method that triggers a request to http://date.jsontest.com/, parses the json response and prints out the current date in a readable format as follows: Monday 14th of August, 2023 - 06:47 PM
<?php 
  function CurrentDate($URL){
    $response = json_decode(APIService::GetData($URL), true);
    if($response && array_key_exists("date",$response) && array_key_exists("time",$response)){
      $date = strtotime($response["date"]." ".$response["time"]);
      $current_date =  date('l jS \of F, Y - h:i A', $date);
      return $current_date; 
    }
    return "Failed to fetch data from ".$URL;
  }
  echo (CurrentDate('http://date.jsontest.com/'));
?>
4) Write a method that triggers a request to http://echo.jsontest.com/john/yes/tomas/no/belen/yes/peter/no/julie/no/gabriela/no/messi/no, parse the json response.
Using that data print two columns of data. The left column should contain the names of the persons that responses 'no',
and the right column should contain the names that responded 'yes'
<?php 
  function PersonsFilter($URL){
    $response = json_decode(APIService::GetData($URL), true);
    if($response && count($response)){
      [$YesNames,$NoNames] = GetFilteredResults($response);
      if(count($YesNames) || count($NoNames)){
        $columnWidth = GetColumnWidth($YesNames,$NoNames);
        echo str_replace('~', '&nbsp;',str_pad("Yes:", $columnWidth,"~")) . str_replace('~', '&nbsp;',str_pad("No:", $columnWidth,"~",STR_PAD_LEFT)) . "<br>";
        for($i=0;$i<max(count($YesNames),count($NoNames));$i++){
          $yesName = isset($YesNames[$i]) ? $YesNames[$i] : '';
          $noName = isset($NoNames[$i]) ? $NoNames[$i] : '';
          echo str_replace('~', '&nbsp;',str_pad($yesName, $columnWidth,"~")) . str_replace('~', '&nbsp;',str_pad($noName, $columnWidth,"~",STR_PAD_LEFT)) . "<br>";
        }
      } else {
        echo "No names found with either yes or no";
      }
    } else {
      echo "Failed to fetch data from ".$URL;
    } 
  }
  function GetFilteredResults($response){
    $YesNames = $NoNames = array();
    foreach($response as $name => $response) {
      if(strtolower($response) == "yes"){
        $YesNames[] = $name;
      } elseif(strtolower($response) == "no"){
        $NoNames[] = $name;
      }
    }
    return [$YesNames,$NoNames];
  }
  function GetColumnWidth($array1,$array2){
    $maxLength1 = max(array_map('strlen', $array1));
    $maxLength2 = max(array_map('strlen', $array2));
    // Calculate the column width based on the maximum name length
    return max($maxLength1, $maxLength2) + 2;
  }
  PersonsFilter('http://echo.jsontest.com/john/yes/tomas/no/belen/yes/peter/no/julie/no/gabriela/no/messi/no');
?>