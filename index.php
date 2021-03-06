<?php include 'class.php'; ?>
<!DOCTYPE html>
<html dir="ltr" lang="en-US">
<head>

  <title>OpenPlans MissionCTRL</title>

  <meta charset="utf-8">
  <meta name="description" content="OpenPlans MissionControl">
  <meta name="author" content="OpenPlans">

  <!--  Mobile Viewport Fix -->
  <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />

  <link rel="stylesheet" href="style.css" type="text/css" media="screen, projection" />

  <!-- jquery -->
  <script src="js/jquery-1.7.2.min.js"></script>
  <script src="js/jquery-ui-1.8.21.custom.min.js"></script>

</head>
<body>

  <div id="page">

    <header id="branding" role="banner" class="clearfix">
      <h1 id="site-title"><span>OpenPlans MissionCTRL</span></h1>

      <nav id="menu">
        <ul>
          <li><a href="#" id="projects-bttn">Edit Projects</a></li>
        </ul>
      </nav>

      <form id="add-person" action="add-person.php" method="post">
        <input id="person-add" type="radio" name="add_v_search" value="add_person" /> <label class="add_v_search" for="person-add" id="add_person_label"><span class="bttn add">+</span><span class="hidden">Add</span></label>
        <input id="person-search" type="radio" name="add_v_search" value="0" checked /> <label class="add_v_search checked" for="person-search" id="search_person_label"><span class="bttn search">?</span><span class="hidden">Find</span></label>
        <input type="text" name="person" placeholder="Find Person..." />
      </form>

      <?php include 'projects-list.php'; ?>
    </header><!-- #branding -->

    <?php include 'team-list.php'; ?>

  </div><!-- #page -->

  <script type="text/javascript">
    $(document).ready(function(){

      // show all projects 
      $("#projects-bttn").click(function() {
        $("#projects").slideToggle("fast", function() {
          var $bumpDown = $("#all-projects").height();
          $('#team').animate({
            marginTop: $bumpDown + "px"
          }, 200, function() {
          });
        });
        return false;
      });


      // show add/delete bttns on hover of teammates
      $(".teammate").live("mouseenter", 
        function() {
          if ( $(this).find("input:focus").length ) {
          } else {
            $(this).find(".add-project-bttn").removeClass("invisible");
          }
        }).live("mouseleave", 
        function() {
          if ( $(this).find("input:focus").length ) {
          } else {
            $(this).find(".add-project-bttn").addClass("invisible");
          }
        }
      );


      // show add-project form on add-project-bttn click
      $(".add-project-bttn").live("click", function(){
        $(this).addClass("hidden");
        $(this).siblings(".add-project form").removeClass("hidden");
        $(this).parent().find(".add-project input[type=text]").focus();
        // hide other people's forms
        $(".teammate .add-project-bttn").not(this).removeClass("hidden").addClass("invisible");
        $(".teammate .add-project-bttn").not(this).siblings(".add-project form").addClass("hidden");
      });


      /* Autocomplete */
  		var availablePeople = [];
      $("h2 span").each(function() {
        var person = $(this);
        availablePeople.push(person.text());
      });
  		$( "#add-person input[type=text]" ).autocomplete({
          source: availablePeople,
          select: function(event, ui) { 
              $("#add-person input[type=text]").val(ui.item.label);
              $("#add-person").submit(); 
          }
      });

      $(".add-project input[type=text]").live("focus", function(){
    		var availableProjects = [];
        $("span.project-name").each(function() {
          var prjct = $(this);
          availableProjects.push(prjct.text());
        });
        $(this).autocomplete({
    			source: availableProjects,
          select: function(event, ui) { 
              $(this).val(ui.item.label);
              $(this).submit(); 
          }
    		});
      });


      /* style add or search radio buttons */
      $('#add_person_label').click(function() {
        $('#search_person_label').removeClass('checked');
        $(this).addClass('checked');
        $('input[name="person"]').attr('placeholder', 'Add Person...')
      });
      $('#search_person_label').click(function() {
        $('#add_person_label').removeClass('checked');
        $(this).addClass('checked');
        $('input[name="person"]').attr('placeholder', 'Find Person...')
      });


      /* AJAX FORM SUBMIT: Add Person */
      $("#add-person").submit(function(e) {
        // stop form from submitting normally
        e.preventDefault(); 
        // get the input values
        var $form = $(this),
            person_term = $form.find( 'input[name="person"]' ).val(),
            url = $form.attr( 'action' ),
            add_person = $('input:radio[name="add_v_search"]:checked').val();;
        // send the data & pull in the results
        $.post( url, { person: person_term, add: add_person },
          function( data ) {
              var content = $( data ).find( '#team' );
              $( "#team-list" ).empty().append( content );
              // custom selector for exact matches, because :contains() returns multiples
              $.expr[':'].containsexactly = function(obj, index, meta, stack) 
              {  
                  return $(obj).text().toLowerCase() === (meta[3]).toLowerCase();
              }; 
              // scroll to & hightlight the new person
              $('html, body').animate({
                scrollTop: $("h2 span:containsexactly('" + person_term + "')").offset().top -200
              }, 500);
              $("h2 span:containsexactly('" + person_term + "')").parents("li.teammate").css('background-color', "#f9f9f9");    
          }
        );
        // reset the form
        this.reset();
        $('#add_person_label').removeClass('checked');
        $('#search_person_label').addClass('checked');
        $('input[name="person"]').attr('placeholder', 'Find Person...')
      });


      /* AJAX FORM SUBMIT: Add Project */
      $( ".add-project" ).live("submit",
        function(e){
          // stop form from submitting normally
          e.preventDefault(); 
          // get the input values
          var $form = $(this),
              project_term = $form.find( 'input[name="project"]' ).val(),
              person_id = $form.find( 'input[name="person_id"]' ).val(),
              url = $form.attr( 'action' );
          // send the data & pull in the results
          $.post( url, { project: project_term, person_id: person_id },
            function( data ) {
              var team_content = $( data ).find( '#team' );
              $( "#team-list" ).empty().append( team_content );

              var projects_content = $( data ).find( '#all-projects' );
              $( "#projects" ).empty().append( projects_content );
            }
          );
        }
      );


      /* AJAX FORM SUBMIT: Delete Person-Project Link */
      $( ".delete-link" ).live("submit",
        function(e){
          // stop form from submitting normally
          e.preventDefault(); 
          // get the input values
          var $form = $(this),
              person_id = $form.find( 'input[name="person_id"]' ).val(),
              project_id = $form.find( 'input[name="project_id"]' ).val(),
              url = $form.attr( 'action' );
          // send the data & pull in the results
          $.post( url, { person_id: person_id, project_id: project_id },
            function( data ) {
              var team_content = $( data ).find( '#team' );
              $( "#team-list" ).empty().append( team_content );

              var projects_content = $( data ).find( '#all-projects' );
              $( "#projects" ).empty().append( projects_content );
            }
          );
        }
      );


      /* AJAX FORM SUBMIT: Delete Project */
      $( ".delete-project" ).live("submit",
        function(e){
          // stop form from submitting normally
          e.preventDefault(); 
          // get the input values
          var $form = $(this),
              person_count = $form.find( 'input[name="person_count"]' ).val(),
              project_id = $form.find( 'input[name="project_id"]' ).val(),
              url = $form.attr( 'action' );
          // send the data & pull in the results
          $.post( url, { person_count: person_count, project_id: project_id },
            function( data ) {
              var team_content = $( data ).find( '#team' );
              $( "#team-list" ).empty().append( team_content );

              var projects_content = $( data ).find( '#all-projects' );
              $( "#projects" ).empty().append( projects_content );
            }
          );
        }
      );


      /* AJAX FORM SUBMIT: Delete Person */
      $( ".delete-person" ).live("submit",
        function(e){
          // stop form from submitting normally
          e.preventDefault(); 
          // get the input values
          var $form = $(this),
              project_count = $form.find( 'input[name="project_count"]' ).val(),
              person_id = $form.find( 'input[name="person_id"]' ).val(),
              url = $form.attr( 'action' );
          // send the data & pull in the results
          $.post( url, { project_count: project_count, person_id: person_id },
            function( data ) {
              var team_content = $( data ).find( '#team' );
              $( "#team-list" ).empty().append( team_content );
            }
          );
        }
      );


      /* AJAX FORM SUBMIT: Star Project */
      $( ".star-project" ).live("submit",
        function(e){
          // stop form from submitting normally
          e.preventDefault(); 
          // get the input values
          var $form = $(this),
              starred = $form.find( 'input[name="starred"]' ).val(),
              project_id = $form.find( 'input[name="project_id"]' ).val(),
              url = $form.attr( 'action' );
          // send the data & pull in the results
          $.post( url, { starred: starred, project_id: project_id },
            function( data ) {
              var team_content = $( data ).find( '#team' );
              $( "#team-list" ).empty().append( team_content );

              var projects_content = $( data ).find( '#all-projects' );
              $( "#projects" ).empty().append( projects_content );
            }
          );
        }
      );


      /* AJAX FORM SUBMIT: Stet Timestamp */
      $( ".stet" ).live("submit",
        function(e){
          // stop form from submitting normally
          e.preventDefault(); 
          // get the input values
          var $form = $(this),
              person_id = $form.find( 'input[name="person_id"]' ).val(),
              url = $form.attr( 'action' );
          // send the data & pull in the results
          $.post( url, { person_id: person_id },
            function( data ) {
              var team_content = $( data ).find( '#team' );
              $( "#team-list" ).empty().append( team_content );
            }
          );
        }
      );


    });
  </script>

</body>
</html>