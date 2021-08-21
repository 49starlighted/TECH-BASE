<?php

//データベース接続の変数をあらかじめ定義する
$dsn = 'データベース名';
$user = 'ユーザー名';
$password = 'パスワード';

?>

<?php

//編集の下準備
$comment_var = "";
$name_var = "";
$num_var = "";
$edit_str = "";

if(!empty($_POST["edit"])){
//入力フォームの名前・コメント欄に該当データを挿入する

    if($_POST["edit"] == null){
        echo "⚠️入力が正常ではありません。<br>";
    }else{
        //MySQLへの接続
        try{$pdo = new PDO(
            $dsn, 
            $user, 
            $password, 
            array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
        }catch(PDOException $e){
            echo 'Connection failed: ' . $e->getMessage();
        }

        //SQL文SELECTの実施(Dbから取り出した数値を変数に代入)
        $id = $_POST["edit"]; 
        $sql = 'SELECT * FROM bulletin_board WHERE id=:id';
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        $results = $stmt->fetchAll(); 
        
        //編集に入力したIDが存在しているかどうか
        if($results != null){

            //編集パスワードの確認
            foreach($results as $row){
                if($row['pass'] == $_POST['edit_pass']){

                    //編集する値をnameとcommentの空欄に挿入
                    foreach ($results as $row){
                        $num_var = $row['id'];
                        $name_var = $row['name'];
                        $comment_var = $row['comment'];
                    }
                    $edit_str = "編集を実施してください。<br>パスワードを変更する場合は、新規のパスワードを入力してください。<br>";

                }else{
                $edit_str = "編集パスワードが正しくありません。<br>";
                }
            }

        }else{
            $edit_str = "⚠️入力された値は、存在しないデータ番号です。<br>";
        }
        
    }

}

?>


<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>mission_501</title>
</head>
<body>
    <form action = "m501.php" method="post">
            <p>名前：<input type="text" name="name" value= "<?php echo $name_var; ?>"></p>
            <p>コメント：<input type="text" name="comment" value="<?php echo $comment_var; ?>"></p>
            <p>パスワード：<input type="text" name="comment_pass"></p>
            <p><input type="submit" value="送信"></p>
            <p>削除番号：<input type="text" name="delete"></p>
            <p>パスワード：<input type="text" name="delete_pass"></p>
            <p><input type="submit" value="削除"></p>
            <p>編集番号：<input type="text" name="edit"></p>
            <p>パスワード：<input type="text" name="edit_pass"></p>
            <p><input type="submit" value="編集"></inutn></p>
            <input type="hidden"  name="hidden" value="<?php echo $num_var; ?>">
    <?php
    //編集に関する文字の出力
    echo $edit_str;

    if(!empty($_POST["name"]) && !empty($_POST["comment"])){
        if($_POST["name"] != null && $_POST["comment"] != null){
        //DBへの書き込み    

            //MySQLへの接続
            try{$pdo = new PDO(
                $dsn, 
                $user, 
                $password, 
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            }catch(PDOException $e){
                echo 'Connection failed: ' . $e->getMessage();
            }

         
            if($_POST["hidden"] == null){
                //通常モードの書き込み
                //tableへの書き込み
                $sql = $pdo -> prepare("INSERT INTO bulletin_board (name, comment, date, pass) VALUES (:name, :comment, :date, :pass)");
                $sql -> bindParam(':name', $_POST["name"], PDO::PARAM_STR);
                $sql -> bindParam(':comment', $_POST["comment"], PDO::PARAM_STR);
                $sql -> bindParam(':date', $date, PDO::PARAM_STR);
                $sql -> bindParam(':pass', $_POST["comment_pass"],PDO::PARAM_STR);

                $date = date("Y/m/d H:i:s");
            
                //処理の実施
                $sql -> execute();

                echo "書き込みが正常に完了しました<br>";

            }else{
                //編集モードの書き込み
                //SQL文SELECTの実施

                //変更する投稿番号
                $id = $_POST["hidden"]; 
                //変更後の名前・コメント・パスワード（入力）
                $name = $_POST["name"];
                $comment = $_POST["comment"]; 
                $pass = $_POST["comment_pass"];
                //変更後の時間
                $date = date("Y/m/d H:i:s");

                if($pass != null){
                    //SQL文UPDATEの実施(パスワード変更あり)
                    $sql = 'UPDATE bulletin_board SET name=:name,comment=:comment,date=:date,pass=:pass WHERE id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                    $stmt->bindParam(':pass', $pass, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();

                    echo "編集が完了しました。<br>パスワードを変更しました。<br>";
                }else{
                    //SQL文UPDATEの実施（パスワードの変更なし）
                    $sql = 'UPDATE bulletin_board SET name=:name,comment=:comment,date=:date WHERE id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':name', $name, PDO::PARAM_STR);
                    $stmt->bindParam(':comment', $comment, PDO::PARAM_STR);
                    $stmt->bindParam(':date', $date, PDO::PARAM_STR);
                    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                    $stmt->execute();

                    echo "編集が完了しました。<br>";
                }
            }


        }else{
            echo "⚠️不明な値が存在しています。<br>";
        }

        
    }elseif(!empty($_POST["delete"])){
    //DBのデータ削除

        //中身が存在しない場合の処理
        if($_POST["delete"] == null){
            echo "⚠️正しい数値を入力してください。";
        }else{
            //MySQLへの接続
            try{$pdo = new PDO(
                $dsn, 
                $user, 
                $password, 
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
            }catch(PDOException $e){
                echo 'Connection failed: ' . $e->getMessage();
            }

            //削除パスワードの確認(SQL文SELECTを実施)
            $id = $_POST["delete"]; 
            $sql = 'SELECT * FROM bulletin_board WHERE id=:id';
            $stmt = $pdo->prepare($sql);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $results = $stmt->fetchAll();

            foreach($results as $row){
                if($row["pass"] == $_POST["delete_pass"]){
                    //SQL文の実施
                    $id = $_POST["delete"];
                    $sql = 'delete from bulletin_board where id=:id';
                    $stmt = $pdo->prepare($sql);
                    $stmt->bindParam(':id',$id, PDO::PARAM_INT);
                    $stmt->execute();

                    echo "削除が完了しました。<br>";
                }else{
                    echo "削除パスワードが正しくありません。<br>";   
                }
            }
        }
    }else{
        echo "";
    }

    //DBのブラウザ表記
    //MySQLへの接続
    try{$pdo = new PDO(
        $dsn, 
        $user, 
        $password, 
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));
    }catch(PDOException $e){
        echo 'Connection failed: ' . $e->getMessage();
    }

    //tableの生成（存在しなければ）
    $sql = "CREATE TABLE IF NOT EXISTS bulletin_board"
    ." ("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name char(32),"
    . "comment TEXT,"
    . "date DATETIME,"
    . "pass TEXT"
    .");";
    $stmt = $pdo->query($sql);

    //ブラウザ表記
    $sql = 'SELECT * FROM bulletin_board';
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll();
    foreach ($results as $row){
        //$rowの中にはテーブルのカラム名が入る
        echo $row['id'].',';
        echo $row['name'].',';
        echo $row['comment'].',';
        echo $row['date'].',';
        echo $row['pass'].'<br>';  
    }
    echo "<hr>";
        
        
    ?>
    
</body>
</html>