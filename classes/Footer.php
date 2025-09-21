<?php

class Footer {

    public function showFooter(){
        echo '
        
        <footer class="footer">
            <p>&copy; ' . date("Y") . ' Story Sphere. All rights reserved.</p>
        </footer>
        ';
    }
}

?>