<?php

require_once( 'db_base.php' );

class generic_interface extends db_base
{
	var $errors;
	function __construct($pref_tablename)
	{
		parent::__construct($pref_tablename);
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
                page(_($this->help_context));
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

                if (isset($_POST['action']))
                {
                        $this->set_var( 'action', $_POST['action'] );
                }
                if (isset($_GET['action']) && $this->found)
                {
                        $this->set_var( 'action', $_GET['action'] );
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
