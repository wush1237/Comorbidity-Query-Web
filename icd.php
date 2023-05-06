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
    <h1>使用ICD查詢</h1>
    <p>如果使用者輸入了一個ICD編碼，則顯示與該編碼相關的前三個共病性疾病及其相關資訊。</p>
    <p>如果使用者輸入了兩個ICD編碼，則顯示這兩個編碼之間的共病性資訊。</p><br>

    <div class="input-container">
      <form method="POST" action="">
        <label>輸入ICD編碼1：</label>
        <input type="text" name="icd1" required>
        <br>
        <label>輸入ICD編碼2：</label>
        <input type="text" name="icd2">
        <br>
        <label for="rows">顯示行數：</label>
        <select name="rows" id="rows">
          <option value="3">3筆</option>
          <option value="5">5筆</option>
          <option value="10">10筆</option>
          <option value="20">20筆</option>
        </select>
        <br><br>
        <input type="submit" name="submit" value="查詢">
      </form>
    </div>
    <div class="result-container">
      <h2>搜索結果：</h2>

      <?php
      if (isset($_POST['submit'])) {
        // 取得使用者輸入的 ICD 編碼
        $icd1 = $_POST['icd1'];
        $icd2 = $_POST['icd2'];
        $rows = $_POST['rows'];

        // 資料庫連線
        $conn = mysqli_connect("localhost", "root", "12345678", "icd_test");
        if (!$conn) {
          die("連線失敗: " . mysqli_connect_error());
        }

        if (!empty($icd1) && empty($icd2)) {
          echo "<h3>ICD編碼: $icd1</h3>";

          // 查詢icd共病性資料
          $sql = "SELECT * FROM icd_rr WHERE ICD2 = '$icd1' ORDER BY RR DESC LIMIT $rows";
          $result = $conn->query($sql);

          // 顯示查詢結果
          if ($result->num_rows > 0) {
            echo "<ul>";
            while ($row = $result->fetch_assoc()) {
              $icd = $row["ICD1"];
              $rr = $row["RR"];
              $icd_name = mysqli_query($conn, "SELECT ICDname FROM icd9toicd10 WHERE icd9code = '$icd'");
              $name = "";
              if (mysqli_num_rows($icd_name) > 0) {
                $row_name = mysqli_fetch_assoc($icd_name);
                $name = $row_name["ICDname"];
              }
              echo "<li> ICD: $icd, $name, RR: $rr</li>";
            }
            echo "</ul>";
          } else {
            echo "查無符合條件的資料";
          }
        }
      }

      // 如果兩個欄位都填，則進行相互的共病性分析
      if (!empty($icd1) && !empty($icd2)) {
        echo "<h3>$icd1,與 $icd2 的共病性關係 <br> </h3>";

        // 查詢icd共病性資料
        $sql = "SELECT * FROM icd_rr WHERE ICD1 = '$icd2' AND ICD2 = '$icd1'";
        $result = $conn->query($sql);

        // 顯示查詢結果
        if ($result->num_rows > 0) {
          echo "<ul>";
          $row = $result->fetch_assoc();
          $icd1 = $row["ICD1"];
          $icd2 = $row["ICD2"];
          $rr = $row["RR"];
          $icd_name1 = mysqli_query($conn, "SELECT ICDname FROM icd9toicd10 WHERE icd9code = '$icd1'");
          $icd_name2 = mysqli_query($conn, "SELECT ICDname FROM icd9toicd10 WHERE icd9code = '$icd2'");
          $name1 = "";
          $name2 = "";
          if (mysqli_num_rows($icd_name1) > 0) {
            $row_name1 = mysqli_fetch_assoc($icd_name1);
            $name1 = $row_name1["ICDname"];
          }
          if (mysqli_num_rows($icd_name2) > 0) {
            $row_name2 = mysqli_fetch_assoc($icd_name2);
            $name2 = $row_name2["ICDname"];
          }
          echo "<li> ICD1: $icd2, $name2 </li>";
          echo "<li>ICD2: $icd1, $name1 </li>";
          echo "<li>RR: $rr</li>";
          echo "</ul>";
        } else {
          echo "查無符合條件的資料";
        }
      }
      ?>
    </div>
  </div>
</body>

</html>