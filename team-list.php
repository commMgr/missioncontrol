<div id="team-list">
  <ul id="team">
    <?php

    $result = $db->query("SELECT * FROM team ORDER BY person");
    $row = array(); 
    $i = 0; 

    while($res = $result->fetchArray()){ 

      if(!isset($res['person'])) continue; 

      $row[$i]['person'] = $res['person']; 

      date_default_timezone_set('America/New_York');
      $timestamp=strtotime($res['updated']);
      $interval = time()-$timestamp;

      ?>
      <li class="teammate clearfix<?php if ( round(abs($interval)/(60*60*24)) >= '8' ) { echo " old"; } if ( round(abs($interval)/(60*60*24)) >= '11' ) { echo " really-old"; } ?>" id="person-<?php echo $res['person_id']; ?>">
        <header class="teammate-info">
          <h2><span><?php echo $res['person']; ?></span><?php 
            if ( round(abs($interval)/(60*60*24)) >= '14' ) { 
              echo "zzz&hellip;"; 
            }
            $the_person_id = $res['person_id'];
            $the_person_rows = $db->query("SELECT count(*) FROM links WHERE person_id = '$the_person_id'");
            $the_person_row = $the_person_rows->fetchArray();
            $numRows = $the_person_row['count(*)'];
            if ( $numRows == "0" ) {
          ?><form class="delete-person" action="delete-person.php" method="post">
              <input type="hidden" name="project_count" value="<?php echo $numRows; ?>" />
              <input type="hidden" name="person_id" value="<?php echo $the_person_id; ?>" />
              <input type="submit" class="delete" value="&times;" />
            </form><?php 
            }
          ?></h2>
          <span class="timestamp"><?php 
            if ( $res['updated'] != NULL ) { 
              echo $res['updated'];
              ?><form class="stet" action="stet.php" method="post">
                  <input type="hidden" name="person_id" value="<?php echo $the_person_id; ?>" />
                  <input type="image" class="stet" src="images/arrow-repeat.png" alt="Refresh Timestamp">
                </form><?php
            } 
          ?>&nbsp;</span>
        </header>
        <ul class="projects">
          
          <?php 

          $person_id = $res['person_id'];

          $project_result = $db->query("SELECT *
            FROM projects
            INNER JOIN links ON projects.project_id = links.project_id
            WHERE (person_id = '$person_id')");

          $project_row = array(); 
          $project_i = 0; 

          while($project_res = $project_result->fetchArray()){ 

            if(!isset($project_res['project'])) continue; 

            $project_row[$i]['project'] = $project_res['project']; 

            ?><li class="project id-<?php echo $project_res['project_id']; ?><?php if ( $project_res['starred'] == '1') { echo " starred"; } ?>">
                <?php if ( $project_res['starred'] == '1') { echo '<span class="star">&#10029;</span>'; } ?>
                <?php echo $project_res['project']; ?> 
                <form class="delete-link" action="delete-link.php" method="post">
                  <input type="hidden" name="person_id" value="<?php echo $res['person_id']; ?>" />
                  <input type="hidden" name="project_id" value="<?php echo $project_res['project_id']; ?>" />
                  <input type="submit" class="delete" value="&times;" />
                </form>
              </li><?php 
            $i++; 

          }

          ?>
          <li class="add-project">
            <span class="add-project-bttn invisible">+<span class="hidden">Add Project:</span></span>
            <form class="add-project hidden" action="add-project.php" method="post">
              <input type="hidden" name="person_id" value="<?php echo $res['person_id']; ?>" />
              <input type="text" name="project" placeholder="Add Project&hellip;"/>
            </form>
          </li>
        </ul>
      </li><?php 

      $i++; 

    }

  ?>
  </ul>
</div>