<!DOCTYPE html>
<html>

<head>
  <title>平台網站</title>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <div id="sidebar">
  <ul>
      <li><a href="index.php">身分證字號查詢</a></li>
      <li><a href="icd.php">ICD共病性查詢</a></li>
      <li><a href="icdName.php">ICD碼查詢</a></li>
      <li><a href="dataChart.php">趨勢圖</a></li>
    </ul>
  </div>
  <div class="container">
    <h1>ICD碼查詢</h1>


    <div class="input-container">
      <form name="icdName" method="POST" action="" onsubmit="return validateForm()">
        <label>輸入關鍵字：</label>
        <input type="text" name="keyword">
        <label>輸入ICD9：</label>
        <input type="text" name="icd9">
        <br>
        <label>輸入ICD10：</label>
        <input type="text" name="icd10">
        <br>
        <input type="submit" name="submit" value="查詢">
      </form>
    </div>


    <div class="existing-icd-container">
      <?php
        // 資料庫連線
        $conn = mysqli_connect("localhost", "root", "12345678", "icd_test");
        if (!$conn) {
          die("連線失敗: " . mysqli_connect_error());
        }

        if (isset($_POST['submit'])) {
          // 取得使用者輸入的關鍵字
          if (isset($_POST['keyword'])) {
            $keyword = $_POST['keyword'];
          } else {
            $keyword = '';
          }
        
          // 取得使用者輸入的ICD9
          if (isset($_POST['icd9'])) {
            $icd9 = $_POST['icd9'];
          } else {
            $icd9 = '';
          }
        
          // 取得使用者輸入的ICD10
          if (isset($_POST['icd10'])) {
            $icd10 = $_POST['icd10'];
          } else {
            $icd10 = '';
          }
        
          // 建立SQL查詢語句
          if (!empty($icd9)) {
            $sql = "SELECT * FROM icd9toicd10 WHERE ICD9code='$icd9'";
          } else
          if (!empty($icd10)) {
            $sql = "SELECT * FROM icd9toicd10 WHERE ICD10code LIKE '%$icd10%'";
          } else {
            $sql = "SELECT * FROM icd9toicd10 WHERE ICDname LIKE '%$keyword%'";
          }
          $result = $conn->query($sql);
        
          // 判斷查詢結果是否存在
          if ($result->num_rows > 0) {
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
              echo "<li> ICD9: " . $row["ICD9code"] . ",  ICD10: " . $row["ICD10code"] . ",  Name: ". $row["ICDname"] . "</li>";
            }
            echo "</ul>";
          } else {
            echo "查無符合條件的資料";
          }
        }
        
        
        
        
        $conn->close();
      ?>
    </div>
</body>

</html>
