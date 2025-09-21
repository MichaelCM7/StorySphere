<?php

class Header {

    public function showHeader(){
        echo '
        <header class="header">
            <div class="logo">Story Sphere</div>
            <nav class="nav">
                <a href="#">Home</a>
                <a href="#">Profile</a>
                <a href="#">Logout</a>
            </nav>
        </header>
        ';
    }
}


?>