<?php

require_once( '../ksf_modules_common/class.generic_fa_interface.php' );
class generic_interface extends generic_fa_interface
{
}

//GI was replaced by g_fa_i... in newer modules.  replacement has been extended.
//This is a work-around until we are happily tested.

//require_once( '../ksf_modules_common/db_base.php' );
//class generic_interface extends db_base
class depreciated_generic_interface extends db_base
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
		echo __FILE__ . ":" . __LINE__ . "<br />\n";
                if ($this->found) {
			echo __FILE__ . ":" . __LINE__ . "<br />\n";
                        $this->loadprefs();
                }
                else
                {
			echo __FILE__ . ":" . __LINE__ . "<br />\n";
                        $this->install();
                        $this->set_var( 'action', "show" );
                }

                if (isset($_POST['action']))
                {
			echo __FILE__ . ":" . __LINE__ . "<br />\n";
                        $this->set_var( 'action', $_POST['action'] );
                }
                if (isset($_GET['action']) && $this->found)
                {
			echo __FILE__ . ":" . __LINE__ . "<br />\n";
                        $this->set_var( 'action', $_GET['action'] );
                }
		echo __FILE__ . ":" . __LINE__ . "<br />\n";

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
