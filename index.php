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
    <h1>使用身分證字號查詢</h1>
    <script>
      function validateForm() {
        var id = document.forms["myForm"]["id"].value;
        if (id == "") {
          alert("身分證字號不得為空");
          return false;
        } else if (!/^[A-Z][1-2]\d{8}$/.test(id)) {
          alert("身分證字號格式不正確，請重新輸入");
          return false;
        }
      }
    </script>


    <div class="input-container">
      <form name="myForm" method="POST" action="" onsubmit="return validateForm()">
        <label>輸入身分證字號：</label>
        <input type="text" name="id">
        <input type="submit" name="submit" value="查詢">
      </form>
    </div>
    <div class="existing-icd-container">
      <?php
      if (isset($_POST['submit'])) {
        $id = $_POST['id'];

        // 資料庫連線
        $conn = mysqli_connect("localhost", "root", "12345678", "icd_test");
        if (!$conn) {
          die("連線失敗: " . mysqli_connect_error());
        }

        // 查詢該患者已被診斷的ICD編碼
        $sql_existing = "SELECT ICD9 FROM tester WHERE CHARTID = '$id'";
        $result_existing = mysqli_query($conn, $sql_existing);
        echo "身分證字號 $id 患者<br>";

        if (mysqli_num_rows($result_existing) > 0) {
          echo "<div class='existing-icd-container'>";
          echo "<h2>已診斷疾病：</h2>";
          echo "<ul>";
          while ($row_existing = mysqli_fetch_assoc($result_existing)) {
            $icd_existing = $row_existing["ICD9"];
            $icd_existing_name = mysqli_query($conn, "SELECT ICDname FROM icd9toicd10 WHERE ICD9code = '$icd_existing'");
            $icd_name = "";
            if (mysqli_num_rows($icd_existing_name) > 0) {
              $row_icd_name = mysqli_fetch_assoc($icd_existing_name);
              $icd_name = $row_icd_name["ICDname"];
            }
            echo "<li>ICD: $icd_existing, $icd_name</li>";
          }
          echo "</ul>";
          echo "</div>";
        } else {
          echo "該病患尚未被診斷任何疾病";
        }
        mysqli_close($conn);
      }
      ?>
    </div>


    <div class="result-container">
      <?php
      if (isset($_POST['submit'])) {
        // 取得使用者輸入的身分證字號
        $id = $_POST['id'];

        // 資料庫連線
        $conn = mysqli_connect("localhost", "root", "12345678", "icd_test");
        if (!$conn) {
          die("連線失敗: " . mysqli_connect_error());
        }

        // 在表格tester搜尋相同身分證字號的ICD編碼
        $sql = "SELECT * FROM tester WHERE CHARTID = '$id'";
        $result = mysqli_query($conn, $sql);

        if (mysqli_num_rows($result) > 0) {
          // 找到了相同身分證字號的ICD編碼，逐一進行第二次搜尋
          $icd_codes = array();
          $diagnosed_icd_codes = array(); // 存儲已被診斷的ICD編碼
          while ($row = mysqli_fetch_assoc($result)) {
            $icd = $row["ICD9"];
            $diagnosed_icd_codes[] = $icd; // 將已被診斷的ICD編碼存儲到陣列中
            // 在表格icd_rr中搜尋該ICD編碼的共病性RR值，但不包含已被診斷的疾病
            $sql2 = "SELECT * FROM icd_rr WHERE ICD2 = '$icd' AND ICD1 NOT IN ('" . implode("','", $diagnosed_icd_codes) . "') ORDER BY RR DESC LIMIT 3";
            $result2 = mysqli_query($conn, $sql2);

            // 將結果存儲到陣列中
            while ($row2 = $result2->fetch_assoc()) {
              $icd_code = $row2["ICD1"];
              $rr = $row2["RR"];
              if (isset($icd_codes[$icd_code])) {
                // 如果該ICD碼已經存在於$icd_codes中，取出目前的RR值比較
                if ($icd_codes[$icd_code] < $rr) {
                  $icd_codes[$icd_code] = $rr; // 更新RR值
                }
              } else {
                // 如果該ICD碼不存在於$icd_codes中，直接加入
                $icd_codes[$icd_code] = $rr;
              }
            }
          }

          // 顯示診斷結果
          echo '<div class="result-container">';
          echo '<h2>診斷結果：</h2>';
          if (count($icd_codes) > 0) {
            arsort($icd_codes); // 將陣列按照 $rr 值由大到小排序
            echo "<ul>";
            foreach ($icd_codes as $icd => $rr) {
              $icd_name = mysqli_query($conn, "SELECT ICDname FROM icd9toicd10 WHERE ICD9code = '$icd'");
              $name = "";
              if (mysqli_num_rows($icd_name) > 0) {
                $row_name = mysqli_fetch_assoc($icd_name);
                $name = $row_name["ICDname"];
              }
              echo "<li> ICD: $icd, $name, RR: $rr</li>";
            }
            echo "</ul>";
          } else {
            $conn->close();
            echo "該病患查無相關共病性資料";
          }
        }
      }
      ?>
    </div>
</body>

</html>