<?php
if (!function_exists('badgeLink')) {
    function badgeLink($file) {
        if ($file != 'X') {
            return "<a href='uploads/{$file}' class='download-badge'>
                        <ion-icon name='download-outline' style='vertical-align: middle;'></ion-icon>Download
                    </a>";
        } else {
            return "<span class='no-file'>X</span>";
        }
    }
}
?>
