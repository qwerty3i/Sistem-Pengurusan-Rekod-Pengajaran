<?php  $conn = new mysqli('localhost', 'root', '', 'rekod_pengurusan_sistem');
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }?>