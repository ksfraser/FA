<?php

require_once( '../ksf_modules_common/db_base.php' );

class generic_interface extends db_base
{
	var $errors;
	var $javascript;
	var $help_context;
	var $page_title;
	function __construct($host, $user, $pass, $database, $prefs_tablename)
	{
		parent::__construct($host, $user, $pass, $database, $prefs_tablename);
	}
        function related_tabs()
        {
                $action = $this->action;
                foreach( $this->tabs as $tab )
                {
                        if( $action == $tab['action'] )
                        {
                                echo $tab['title'];
                                echo '&nbsp;|&nbsp;';
                        }
                        else
                        {
                                if( $tab['hidden'] == FALSE )
                                {
                                        hyperlink_params($_SERVER['PHP_SELF'],
                                                _("&" .  $tab['title']),
                                                "action=" . $tab['action'],
                                                false);
                                        echo '&nbsp;|&nbsp;';
                                }
                        }
                }
        }
        function show_form()
        {
                $action = $this->action;
                foreach( $this->tabs as $tab )
                {
                        if( $action == $tab['action'] )
                        {
                                //Call appropriate form
                                $form = $tab['form'];
                                $this->$form();
                        }
                }
        }
        function base_page()
        {
                //page(_($this->help_context));
		page( $this->page_title, false, false, "", $this->javascript);
                $this->related_tabs();
        }
        function display()
        {
                $this->base_page();
                $this->show_form();
                end_page();
        }
        function run()
        {
                if ($this->found) {
                        $this->loadprefs();
                }
                else
                {
                        $this->install();
                        $this->set_var( 'action', "show" );
                }
		//var_dump( $_POST );
		//var_dump( $_GET );
                if (isset($_POST['action']))
                {
                        $this->set_var( 'action', $_POST['action'] );
                }
		else if (isset($_GET['action']) && $this->found)
                {
                        $this->set_var( 'action', $_GET['action'] );
		}
		else
		{
			//action not set, so we passed in a Button
			foreach( $this->tabs as $row )
			{
				if( isset( $_POST[$row['action']] ) )
				{
					$this->set_var( 'action', $row['action'] );
					//echo "Set action to " . $row['action'] . " <br />";
					continue;
				}
			}
		}
                $this->display();
        }
	function append_file( $filename )
	{
		$fp = fopen( $filename, 'a' );
		return $fp;
	}
	function overwrite_file( $filename )
	{
		$fp = fopen( $filename, 'w' );
		return $fp;
	}
	function close_file( $fp )
	{
		fflush( $fp );
		fclose( $fp );
	}



}

?>
