<?php

class Functions
{
    private $conn;
    private $users;
  /*  private $blog_posts;
    private $comment;
    private $likeDislike;
    private $comment_reply;*/

    function __construct()
    {
        require_once "connection.php";
        $db = new connection();
        $this->conn = $db->DBConnect();
        $this->users = "users";
       /* $this->blog_posts = "posts";
        $this->comment = "comment";
        $this->likeDislike = "likeDislike";
        $this->comment_reply = "comment_reply";*/
    }

    function insertUserData($id, $name, $userid, $email, $password, $phone)
    {
        try {
            $this->createTable();
            if ($id == "") {
                $dataQuery = "INSERT INTO $this->users( name, userid, email,password, phone) VALUES (:name,:userid,:email,:password,:phone)";
            } else {
                $dataQuery = "update $this->users set name=:name ,userid=:userid ,email=:email ,password=:password ,phone=:phone where id=:id";
            }

            $stmt = $this->conn->prepare($dataQuery);
            if (is_numeric($id))
                $stmt->bindParam(":id", $id);
            $pass = password_hash($password, PASSWORD_BCRYPT);
            $stmt->bindParam(":name", $name);
            $stmt->bindParam(":userid", $userid);
            $stmt->bindParam(":email", $email);
            $stmt->bindParam(":password", $pass);
            $stmt->bindParam(":phone", $phone);
            $stmt->execute();

            if ($id == "") {
                $msg = "Data Not Inserting";
                if (isset($stmt))
                    $msg = "Data Insert Successfully";
            } else {
                $msg = "Data Not Updating";
                if (isset($stmt))
                    $msg = "Data Update Successfully";
            }
            return $msg;
        } catch (PDOException  $e) {
            echo $e->getMessage();
        }
    }

    function createUserTable()
    {

        $createTable = "
                CREATE TABLE IF NOT EXISTS $this->users(
                    id int primary key AUTO_INCREMENT,
                    name varchar(25) not null,
                    userid varchar(25) not null,
                    email varchar(50) not null,
                    password char(60) not null,
                    phone varchar(10) not null
                )
                ";

        if ($this->conn->exec($createTable)) {
            echo "<script>console.log(" . json_encode('User Table Created', JSON_HEX_TAG) . ")</script>";
        }
    }

    function getUserData($id)
    {
        if ($id == "") {
            $getData = "Select * from $this->users";
        } else {
            $getData = "Select * from $this->users where id=:id";
        }
        $stmt = $this->conn->prepare($getData);
        if (isset($id))
            $stmt->bindParam(":id", $id);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }

    function deleteUserData($id)
    {

        $deleteData = "delete from $this->users where id=:id";
        $stmt = $this->conn->prepare($deleteData);
        $stmt->bindParam(":id", $id);
        $msg = "Data Not Deleted";
        if ($stmt->execute())
            $msg = "Data Deleted Successfully";
        return $msg;
    }

    function checkUserEmail($email)
    {

        $this->createTable();
        $checkEmail = "select * from $this->users where email=:email";
        $stmt = $this->conn->prepare($checkEmail);
        $stmt->bindParam(":email", $email);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $row = $stmt->fetchAll();
        return $stmt->rowCount();

    }

    function checkUserEmailAndPassword($email, $password)
    {

        $checkEmail = "select id,name,email,password from $this->users where email=:email";
        $stmt = $this->conn->prepare($checkEmail);
        $stmt->bindParam(":email", $email);
        //$stmt->bindParam(":password",$password);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $val = $stmt->fetch();
        if (isset($password) && isset($val['password'])) {
            if (password_verify($password, $val['password'])) {
                return $val;
            }
        }
    }

  /*  function dataValidation($name, $userid, $email, $password, $phone)
    {

        $chk = array();
        if (empty($name)) {
            $chk["name"] = "Name is Required";
        }
        if (empty($userid)) {
            $chk["userid"] = "userid is Required";
        }
        if (empty($email)) {
            $chk["email"] = "Email Id is Required";
        }
        if (empty($password)) {
            $chk["password"] = "Password is Required";
        } else if (!empty($password)) {
            $len = strlen($password);
            if (16 < $len or 8 > $len) {
                $chk["password"] = "Please enter password in between 8 to 16 character";
            }
        }
        if (empty($phone)) {
            $chk["phone"] = "Contact No. is Required";
        } else if (!empty($phone)) {
            $num = is_numeric($phone);
            $len = strlen($phone);
            if ($num == false) {
                $chk["phone"] = "Please enter number in digits";
            } else if (10 < $len or 10 > $len) {
                $chk["phone"] = "Please enter 10 digit number";
            }
        }
        return $chk;
    }

    function blogDataValidation($title, $desc)
    {
        $chk = array();
        if (empty($title)) {
            $chk["title"] = "Title is Required";
        }
        if (empty($desc)) {
            $chk["desc"] = "Description is Required";
        }
        return $chk;
    }

    function createBlogTable()
    {
        try {
            $createBlogTable = "
                CREATE TABLE IF NOT EXISTS `posts` (
                    `id` int(11) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                    `user_id` int(11) DEFAULT NULL,
                    `title` varchar(255) NOT NULL,
                    `views` int(11) NOT NULL DEFAULT 0,
                    `image` varchar(255)  NULL,
                    `body` text NOT NULL,
                    `published` tinyint(1) NOT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                     FOREIGN KEY (`user_id`) REFERENCES $this->users (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                ) ENGINE=InnoDB DEFAULT CHARSET=latin1;
                    ";

            if ($this->conn->exec($createBlogTable)) {
                echo "<script>console.log(" . json_encode('Blog Table Created', JSON_HEX_TAG) . ")</script>";
            }
        } catch (PDOException  $e) {
            echo $e->getMessage();
        }

    }

    function insertBlogData($id, $user_id, $title, $img, $body, $publish)
    {
        try {
            $this->createBlogTable();
            if ($id == "" && $img != "") {
                $dataQuery = "INSERT INTO $this->blog_posts  (user_id,title, image, body, published,created_at) VALUES (:userid,:title,:img,:body,:publish,:created)";

            } else if ($id == "" && $img == "") {
                echo "nd";
                //die();
                $dataQuery = "INSERT INTO $this->blog_posts  (user_id,title, body, published,created_at) VALUES (:userid,:title,:body,:publish,:created)";

            } else if ($img != "") {
                $dataQuery = "update $this->blog_posts set title=:title ,image=:img ,body=:body,published=:publish where id=:id";
            } else {
                $dataQuery = "update $this->blog_posts set title=:title ,body=:body,published=:publish where id=:id";
            }

            $stmt = $this->conn->prepare($dataQuery);

            if (is_numeric($id)) {

                $stmt->bindParam(":id", $id);
            } else if ($id == "") {

                $stmt->bindParam(":userid", $user_id);

                $t = time();
                date_default_timezone_set("Asia/Kolkata");
                $create = date("Y-m-d H:i:s", $t);
                $stmt->bindParam(":created", $create);
            }
            if ($img != "")
                $stmt->bindParam(":img", $img);

            $stmt->bindParam(":title", $title);
            $stmt->bindParam(":body", $body);
            $stmt->bindParam(":publish", $publish);

            $stmt->execute();
            if ($id == "") {
                $msg = "Blog Data Not Inserting";
                if (isset($stmt))
                    $msg = "Blog Upload Successfully";
            } else {
                $msg = "Data Not Updating";
                if (isset($stmt))
                    $msg = "Blog Update Successfully";
            }
            return $msg;
        } catch (PDOException  $e) {
            echo $e->getMessage();
        }
    }

    function getLastId(){
        $getCountId = "select count(id) from posts where published = 1";
        $stmt = $this->conn->prepare($getCountId);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        return $stmt->fetchAll();
    }
    function getBlogData($id, $str)
    {
        try {
            if ($id == "" and $str == "") {
                $getBlogData = "Select s.name,b.id,b.user_id,b.title,b.image,b.body,b.published,b.created_at from $this->blog_posts b inner join $this->users s on b.user_id=s.id where published=1 ORDER BY created_at DESC LIMIT 3 ";
            } else if ($id == "" and is_numeric($str)) {
                $getBlogData = "Select s.name,b.id,b.user_id,b.title,b.image,b.body,b.published,b.created_at from $this->blog_posts b inner join $this->users s on b.user_id=s.id where published=1 ORDER BY created_at DESC LIMIT 3 offset :str";
            } else if (is_numeric($id) and $str == "") {
                $getBlogData = "Select * from $this->blog_posts where user_id=:id ORDER BY created_at DESC";
            } else if ($id == "" and $str == "popular") {
                $getBlogData = "select * from $this->blog_posts where published=1 order by views DESC LIMIT 5";
            } else if ($id == "" and $str == "recent") {
                $getBlogData = "select * from $this->blog_posts  where published=1 order by created_at DESC LIMIT 5";
            } else if (is_numeric($id) and $str == "update") {
                $getBlogData = "select * from $this->blog_posts  where id=:id";
            } else if (is_numeric($id) and $str == "blogpage") {
                $getBlogData = "Select s.name,b.id,b.user_id,b.title,b.image,b.body,b.published,b.created_at from $this->blog_posts b inner join $this->users s on b.user_id=s.id where b.id=:id";
            }
            $stmt = $this->conn->prepare($getBlogData);
            if (is_numeric($id)) {
                $stmt->bindParam(":id", $id);
            }
            if(is_numeric($str)){
                $stmt->bindParam(":str", $str,PDO::PARAM_INT);
            }
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            return $stmt->fetchAll();
        } catch (PDOException  $e) {
            echo $e->getMessage();
        }
    }

    function deleteBlogData($id)
    {

        $deleteData = "delete from $this->blog_posts where id=:id";
        $stmt = $this->conn->prepare($deleteData);
        $stmt->bindParam(":id", $id);
        $msg = "Blog Not Deleted";
        if ($stmt->execute())
            $msg = "Blog Deleted Successfully";
        return $msg;
    }

    function createCommentTable()
    {
        try {
            $createBlogTable = "
                CREATE TABLE IF NOT EXISTS `comment` (
                     `id` INT NOT NULL AUTO_INCREMENT ,
                     `bid` INT NOT NULL , `uid` INT NOT NULL ,
                     `comment` TEXT NOT NULL ,
                     `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                     `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                     PRIMARY KEY (`id`),
                    FOREIGN KEY (`bid`) REFERENCES $this->blog_posts (`id`) ON DELETE CASCADE  ON UPDATE NO ACTION,
                    FOREIGN KEY (`uid`) REFERENCES $this->users (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                    ) ENGINE = InnoDB";

            if ($this->conn->exec($createBlogTable)) {
                echo "<script>console.log(" . json_encode('Comment Table Created', JSON_HEX_TAG) . ")</script>";
            }
        } catch (PDOException  $e) {
            echo $e->getMessage();
        }

    }

    function insertCommentData($bid, $uid, $comment)
    {
        try {
            $this->createCommentTable();
            $dataQuery = "INSERT INTO $this->comment  (bid,uid,comment,created_at) VALUES (:bid,:uid,:comment,:created)";

            $stmt = $this->conn->prepare($dataQuery);

            $stmt->bindParam(":bid", $bid);
            $stmt->bindParam(":uid", $uid);
            $stmt->bindParam(":comment", $comment);
            $t = time();
            date_default_timezone_set("Asia/Kolkata");
            $create = date("Y-m-d H:i:s", $t);
            $stmt->bindParam(":created", $create);

            $stmt->execute();
            $msg = "Something is wrong to post comment";
            if (isset($stmt))
                $msg = "Comment Post Successfully";
            return $msg;
        } catch (PDOException  $e) {
            echo $e->getMessage();
        }
    }

    function getSetViewsCounter($bid, $str)
    {
        try {
            if ($str == "first") {
                $getSetViews = "UPDATE posts SET views = (( SELECT views FROM posts where id = :bid ) + 1) WHERE id = :bid";
                $stmt = $this->conn->prepare($getSetViews);
                $stmt->bindParam(":bid", $bid);
                $stmt->execute();
                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                $stmt->fetchAll();
                return $this->getSetViewsCounter($bid, "sec");
            }
            if ($str == "sec") {
                $getCounter = "select views from $this->blog_posts where id = :bid";
                $stmt1 = $this->conn->prepare($getCounter);
                $stmt1->bindParam(":bid", $bid);
                $stmt1->execute();
                $stmt1->setFetchMode(PDO::FETCH_ASSOC);
                return $stmt1->fetch();
            }
        } catch (PDOException  $e) {
            echo $e->getMessage();
        }
    }

    function getCommentData($bid)
    {
        try {

            $getBlogData = "select sd.name,c.id,c.bid,c.uid,c.comment,c.created_at,c.updated_at from $this->comment c inner join $this->users sd on sd.id = c.uid where bid=:bid order by created_at ASC";
            $stmt = $this->conn->prepare($getBlogData);
            $stmt->bindParam(":bid", $bid);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            return $stmt->fetchAll();
        } catch (PDOException  $e) {
            echo $e->getMessage();
        }
    }

    function deleteCommentData($id)
    {

        $deleteData = "delete from $this->comment where bid=:id";
        $stmt = $this->conn->prepare($deleteData);
        $stmt->bindParam(":id", $id);
        $msg = "Comment Not Deleted";
        if ($stmt->execute())
            $msg = "Comment Deleted Successfully";
        return $msg;
    }

    function deleteLikeDislikeData($id)
    {

        $deleteData = "delete from $this->likeDislike where bid=:id";
        $stmt = $this->conn->prepare($deleteData);
        $stmt->bindParam(":id", $id);
        $msg = "Comment Not Deleted";
        if ($stmt->execute())
            $msg = "Comment Deleted Successfully";
        return $msg;
    }


    function createLikeDislikeTable()
    {
        try {
            $createBlogTable = "
                CREATE TABLE IF NOT EXISTS `likeDislike` (
                     `id` INT NOT NULL AUTO_INCREMENT ,
                     `bid` INT NOT NULL , 
                     `uid` INT NOT NULL ,
                     `like` INT NOT NULL DEFAULT 0,
                     `dislike` INT NOT NULL DEFAULT 0,
                     PRIMARY KEY (`id`),
                    FOREIGN KEY (`bid`) REFERENCES $this->blog_posts (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
                    FOREIGN KEY (`uid`) REFERENCES $this->users (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
                    ) ENGINE = InnoDB";

            if ($this->conn->exec($createBlogTable)) {
                echo "<script>console.log(" . json_encode('LikeDislike Table Created', JSON_HEX_TAG) . ")</script>";
            }
        } catch (PDOException  $e) {
            echo $e->getMessage();
        }

    }

    function getTotalCounterComment($bid)
    {
        $dataQuery = "SELECT count(id) as counter FROM $this->comment WHERE bid=:bid";
        $stmt = $this->conn->prepare($dataQuery);
        $stmt->bindParam(":bid", $bid);
        $stmt->execute();
        if (isset($stmt))
            return $stmt->fetchAll();
    }

    function getUserLikeDislikeData($bid)
    {
        $dataQuery = "SELECT sum(`like`) as lk , sum(dislike) as dl,uid FROM likeDislike WHERE bid=:bid";
        $stmt = $this->conn->prepare($dataQuery);
        $stmt->bindParam(":bid", $bid);
        $stmt->execute();
        if (isset($stmt))
            return $stmt->fetchAll();
    }

    function LikeDislikeData($bid, $uid, $str)
    {
        try {
            $this->createLikeDislikeTable();
            if ($str != "get")
                $res = $this->LikeDislikeData($bid, $uid, "get");
            if ($res[0]["like"] == null || $res[0]["like"] == 0) {
                if ($str == "like" && $res[0]["like"] == null) {
                    $dataQuery = "INSERT INTO $this->likeDislike  (bid,uid,`like`) VALUES (:bid,:uid,1)";
                }
            } else
                if ($res[0]["like"] != null && $res[0]["like"] != 0) {
                    $dataQuery = "update $this->likeDislike set `like`=0 , dislike=1 where bid=:bid and uid=:uid";
                }

            if ($res[0]["dislike"] == null || $res[0]["dislike"] == 0) {
                if ($str == "dislike" && $res[0]["dislike"] == null) {
                    $dataQuery = "INSERT INTO $this->likeDislike  (bid,uid,dislike) VALUES (:bid,:uid,1)";
                }
            } else if ($res[0]["dislike"] != null && $res[0]["dislike"] != 0) {
                $dataQuery = "update $this->likeDislike set `like`=1, dislike=0 where bid=:bid and uid=:uid";
            }

            if ($str == "get") {
                $dataQuery = "SELECT `like`,dislike,uid FROM likeDislike WHERE bid=:bid and uid=:uid";
            }
            $stmt = $this->conn->prepare($dataQuery);
            $stmt->bindParam(":bid", $bid);
            $stmt->bindParam(":uid", $uid);
            $stmt->execute();
            $msg = "Something is wrong";
            if (isset($stmt))
                $msg = $stmt->fetchAll();
            return $msg;
        } catch
        (PDOException  $e) {
            echo $e->getMessage();
        }
    }

    function createCommentReplyTable()
    {
        try {
            $createBlogTable = "
                CREATE TABLE IF NOT EXISTS `comment_reply` (
                     `id` INT NOT NULL AUTO_INCREMENT ,
                     `bid` INT NOT NULL , 
                     `uid` INT NOT NULL ,
                     `cid` INT NOT NULL ,
                     `subcomment` TEXT NOT NULL ,
                     `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                     `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
                     PRIMARY KEY (`id`),
                    FOREIGN KEY (`bid`) REFERENCES $this->blog_posts (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
                    FOREIGN KEY (`uid`) REFERENCES $this->users (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
                    FOREIGN KEY (`cid`) REFERENCES $this->comment (`id`) ON DELETE CASCADE ON UPDATE NO ACTION
                    ) ENGINE = InnoDB";

            if ($this->conn->exec($createBlogTable)) {
                echo "<script>console.log(" . json_encode('Comment Table Created', JSON_HEX_TAG) . ")</script>";
            }
        } catch (PDOException  $e) {
            echo $e->getMessage();
        }

    }

    function insertCommentReplyData($bid, $uid,$cid,$reply)
    {
        try {
            $this->createCommentReplyTable();
            $dataQuery = "INSERT INTO $this->comment_reply  (bid,uid,cid,subcomment,created_at) VALUES (:bid,:uid,:cid,:subcomment,:created)";

            $stmt = $this->conn->prepare($dataQuery);

            $stmt->bindParam(":bid", $bid);
            $stmt->bindParam(":uid", $uid);
            $stmt->bindParam(":cid", $cid);
            $stmt->bindParam(":subcomment", $reply);
            $t = time();
            date_default_timezone_set("Asia/Kolkata");
            $create = date("Y-m-d H:i:s", $t);
            $stmt->bindParam(":created", $create);

            $stmt->execute();
            $msg = "Something is wrong to post comment";
            if (isset($stmt))
                $msg = "Comment Post Successfully";
            return $msg;
        } catch (PDOException  $e) {
            echo $e->getMessage();
        }
    }

    function getCommentReplyData($bid,$cid)
    {
        try {
            $this->createCommentReplyTable();
            $getBlogData = "select sd.name,cr.id,cr.bid,cr.uid,cr.subcomment,cr.created_at,cr.updated_at from $this->comment_reply cr inner join $this->users sd on sd.id = cr.uid where cr.bid=:bid and cr.cid=:cid order by created_at ASC";
            $stmt = $this->conn->prepare($getBlogData);
            $stmt->bindParam(":bid", $bid);
            $stmt->bindParam(":cid", $cid);
            $stmt->execute();
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            return $stmt->fetchAll();
        } catch (PDOException  $e) {
            echo $e->getMessage();
        }
    }*/
}