<?php
/*
* To change this license header, choose License Headers in Project Properties.
* To change this template file, choose Tools | Templates
* and open the template in the editor.
*/


global $wpdb;
global $category_data;
global $active_jobs_media;
global $active_jobs_post_status;
global $active_jobs_post_category;

?>

<h1 class="home-header">Test plugin</h1>

<ul class="nav nav-tabs">
    <li class="active"><a data-toggle="tab" href="#media">Media</a></li>
    <li><a data-toggle="tab" href="#posts">Posts</a></li>
    <li><a data-toggle="tab" href="#activeJobs">Active Jobs</a></li>
</ul>

<div class="tab-content">
    <div id="media" class="tab-pane fade in active">
    <h3>Media</h3>
    <!-- Input form to delete media -->
    <form action="#" method="post">
        <p class="input-number">Choose format:</p>
        <select name="chooseAttachment">
            <option value="allAttachment">All</option>
            <option value="image">Images</option>
            <option value="video">Videos</option>
            <option value="audio">Audio (mp3, m4a, wav)</option>
            <option value="text">Text (csv, txt)</option>
            <option value="pdf">PDF</option>
        </select>
        <br>
        <p class="input-number">Input days/months:</p>
        <input type="number" name="days" min=1>
        <select name="chooseFormat">
            <option value="DAY">Days</option>
            <option value="MONTH">Months</option>
        </select>
        <input type="checkbox" name="dailyMedia" value="dailyMedia"> Repeat every day<br><br>
        <select name="deleteFrom">
            <option value="database">Delete only from database</option>
            <option value="server">Delete from server & database</option>
        </select>

        <input type="submit" class="btn btn-outline-success" name="submitMedia" value="Submit" />
    </form>
    <!-- End of input form to delete media -->
    </div>

    <div id="posts" class="tab-pane fade">
    <h3>Posts</h3>
    <form action="#" method="post">
        <p class="input-number">Input days/months:</p>
        <input type="number" name="inputPostDays" min=1>
        <select name="choosePostDateFormat">
            <option value="DAY">Days</option>
            <option value="MONTH">Months</option>
        </select>
        <input type="checkbox" name="dailyPostStatus" value="dailyPost"> Repeat every day<br><br>
        <select name="postStatus">
            <option value="trash">Trash</option>
            <option value="draft">Draft</option>
            <option value="private">Private</option>
        </select>
        <input type="submit" class="btn btn-outline-success" name="submitPostStatus" value="Submit" />
    </form>
    <br><br>
    <form action="#" method="post">
        <p class="input-number">Input days/months:</p>
        <input type="number" name="inputDays" min=1>
        <select name="chooseDateFormat">
            <option value="DAY">Days</option>
            <option value="MONTH">Months</option>
        </select>
        <input type="checkbox" name="dailyCategory" value="dailyMonthly"> Repeat every day<br><br>
        <p class="category-text">Change post category from:</p>
        <select name="changeFrom">
            <?php foreach($category_data  as $category) { ?>
                <option  value="<?php echo $category->term_id?>"><?php echo  $category->name ?> </option>
            <?php } ?>
        </select>
        <p class="category-text">to:</p>
        <select name="changeTo">
            <?php foreach($category_data  as $category) { ?>
                <option  value="<?php echo $category->term_id?>"><?php echo  $category->name ?> </option>
            <?php } ?>
        </select>
        <input type="submit" class="btn btn-outline-success" name="submitCategory" value="Submit" />
    </form>

    </div>
    <div id="activeJobs" class="tab-pane fade">
       <h3>Media active jobs</h3>
       <table class="displayTable">
            <tr>
                <th>Number of days</th>
                <th>Format date</th>
                <th>Delete from</th>
                <th>Attachment</th>
                <th>Options</th>
            </tr>
            <?php foreach( $active_jobs_media as $active_jobs ){
                echo "<tr>";
                echo "<input type='hidden' name='$active_jobs->id' value=". $active_jobs->id .">";
                echo "<td>".$active_jobs->mediaDays."</td>";
                echo "<td>".$active_jobs->formatDate."</td>";
                echo "<td>".$active_jobs->deleteFrom."</td>";
                echo "<td>".$active_jobs->attachmentOption."</td>";
                echo "<td>";
                    echo "<form action='#' Method='POST'> "; 
                    echo "<input type=submit name=remove value='Delete'>";
                    if(isset($_POST['remove'])){ 
                        $delete_row = $wpdb->get_results("DELETE FROM `wp_testplugin` WHERE `wp_testplugin`.`id`='$active_jobs->id'");
                    }
                    echo "</form>";
                echo "</td>";
                echo "</tr>";   
            }?>
       </table>
       <h3>Post status active jobs</h3>
       <table class="displayTable">
            <tr>
                <th>Number of days</th>
                <th>Format date</th>
                <th>Change post status to</th>
                <th>Options</th>
            </tr>
            <?php foreach( $active_jobs_post_status as $active_jobs ){
                echo "<tr>";
                echo "<input type='hidden' name='$active_jobs->id' value=". $active_jobs->id .">";
                echo "<td>".$active_jobs->postStatusDays."</td>";
                echo "<td>".$active_jobs->formatDatePostStatus."</td>";
                echo "<td>".$active_jobs->newPostStatus."</td>";
                echo "<td>";
                    echo "<form action='#' Method='POST'> "; 
                    echo "<input type=submit name=remove value='Delete'>";
                    if(isset($_POST['remove'])){ 
                        $delete_row = $wpdb->get_results("DELETE FROM `wp_testplugin` WHERE `wp_testplugin`.`id`='$active_jobs->id'");
                    }
                    echo "</form>";
                echo "</td>";
                echo "</tr>";
            }?>
       </table>
       <h3>Change post category active jobs</h3>
       <table class="displayTable">
            <tr>
                <th>Number of days</th>
                <th>Format date</th>
                <th>Change post category from</th>
                <th>Change post category to</th>
                <th>Options</th>
            </tr>
            <?php foreach( $active_jobs_post_category as $active_jobs ){
                echo "<tr>";
                echo "<input type='hidden' name='$active_jobs->id' value=". $active_jobs->id .">";
                echo "<td>".$active_jobs->numberPosts."</td>";
                echo "<td>".$active_jobs->formatDatePostCategory."</td>";
                echo "<td>".$active_jobs->fromCategory."</td>";
                echo "<td>".$active_jobs->toCategory."</td>";
                echo "<td>";
                    echo "<form action='#' Method='POST'> "; 
                    echo "<input type=submit name=remove value='Delete'>";
                    if(isset($_POST['remove'])){ 
                        $delete_row = $wpdb->get_results("DELETE FROM `wp_testplugin` WHERE `wp_testplugin`.`id`='$active_jobs->id'");
                    }
                    echo "</form>";
                echo "</td>";
                echo "</tr>";
            }?>
       </table>
    </div>
</div>

<?php
global $wpdb;
// Delete media from server & database
if(isset($_POST['submitMedia'])){
    delete_media($wpdb);
}
// Change post status
if(isset($_POST['submitPostStatus'])){
  
    change_post_status($wpdb);
    
}
// Change post category
if(isset($_POST['submitCategory'])){
        change_category($wpdb);
}