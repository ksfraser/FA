<?php

/*****************************************************
* Requires 3 steps:

    Create ‘Meeting’ object
    Create ‘Meeting’ to ‘Contact’ relation
    Create ‘Meeting’ to ‘User’ relation

*****************************************************/

require_once( 'class.suitecrm.php' );	//class suitecrm

class suitecrm_calendar extends suitecrm
{
	protected $date_start;
	protected $assigned_user_id;	//$user_id
	protected $duration_minutes;
	protected $description;
	protected $name;
	protected $status; 		//Planned
	protected $parent_type;		//'Contacts'
	protected $parent_id;		//$contact_id
	protected $meeting_id;		//what is returned on the create....
    function __construct( $url, $username, $password )
    {
            parent::__construct( $url, $username, $password, "Meetings" );
    }
/*****************
* PERL generic relationship code...
sub create_module_links {
    my ($self, $module, $module_id, $link_field_name, $related_ids, $attributes) = @_;
 
    foreach my $required_attr (@{$self->required_attr->{$module}}) {
        $self->log->logconfess("No $required_attr attribute. Not creating links in $module for: ".Dumper($attributes))
            if (!exists($$attributes{$required_attr}) ||
                !defined($$attributes{$required_attr}) ||
                $$attributes{$required_attr} eq '');
    }
 
    my $rest_data = '{"session": "'.$self->sessionid.'", "module_name": "'.$module
        . '", "module_id": "' . $module_id . '", "link_field_name": "'
        . $link_field_name. '", "related_ids": ' . encode_json($related_ids)
        . ', "name_value_list": '. encode_json($attributes). '}';
 
    my $response = $self->_rest_request('set_relationship', $rest_data);
    $self->log->info( "Successfully created link in <".encode_json($attributes)."> with sessionid ".$self->sessionid."\n");
    $self->log->debug("Link created in module $module was:".Dumper($response));
    #return $response->{id};
    return $response;
*******************/
    function create()
    {
            $val = array();
            foreach( get_object_vars( $this ) as $key => $value )
            {
                    $val[] = array( "name" => $key, "value" => $value );
            }
            $parameters = array(
                    "session" => $this->session_id,
                    "module_name" => $this->module_name,
                //Record attributes
                    "name_value_list" => $val
                        //array(
                                //array("name" => "name", "value" => "Test Account"),
                        //),
                );
            $result = $this->call("set_entry", $parameters);
            $this->id = $result->id;
                echo "<pre>";
                print_r($result);
                echo "</pre>";

/************************************
*the 2 steps for relationships...

my $link_entry = {
        module => 'Meetings',
        meeting_id => $meeting_id,
        module => 'Contacts',
        contact_id => $contact_list[$i]->{'id'}
    };
 
my $out = $s->create_module_links('Meetings',$meeting_id,'contacts',[$contact_id],$link_entry);
 
my $user_link_entry = {
    meeting_id => $meeting_id,
    user_id => $user_id,
    accept_status => 'accept',
    required => 1
};
 
$out = $s->create_module_links('Meetings',$meeting_id,'users',[$user_id],$user_link_entry);

***************************************************/
    }
    function update()
    {
            $parameters = array(
                    "session" => $this->session_id,
                    "module_name" => $this->module_name,
                //Record attributes
                    "name_value_list" => array(
                            array("name" => "id", "value" => "9b170af9-3080-e22b-fbc1-4fea74def88f"),
                            array("name" => "name", "value" => "Test Account"),
                        ),
                );
                $result = $this->call("set_entry", $parameters);
                /*echo "<pre>";
                print_r($set_entry_result);
       }
}

$cl = new suitecrm_calendar("http://fhsws001/devel/fhs/SuiteCRM/service/v4_1/rest.php", "admin", "m1l1ce" );
$cl->login();

/**Example from suitecrm class file
    //create account -------------------------------------
    $set_entry_parameters = array(
         //session id
         "session" => $cl->session_id,

         //The name of the module from which to retrieve records.
         "module_name" => "Accounts",

         //Record attributes
         "name_value_list" => array(
              //to update a record, you will nee to pass in a record id as commented below
              //array("name" => "id", "value" => "9b170af9-3080-e22b-fbc1-4fea74def88f"),
              array("name" => "name", "value" => "Test Account"),
         ),
    );

    $set_entry_result = $cl->call("set_entry", $set_entry_parameters);

    echo "<pre>";
    print_r($set_entry_result);
    echo "</pre>";
*/


if($cl->session_id){
	$set_relationship_parameters = array(
		'session' => $session_id,
		'module_name' => 'Meetings',
		'module_id' => 'ede7c2ad-f180-39ef-571a-563b6ab6f10e',
		'link_field_name' => 'users',
		'related_ids' => array(
			'1',
		),
		'name_value_list' => array(
			array(
				'name' => 'accept_status',
				'value' => 'none'
			),
		),
		'delete'=> 0,
	);
	$set_relationship_result = call('set_relationship', $set_relationship_parameters, $url);
	print_r($set_relationship_result);
}

/******** Meetings
$set_contact_parameters = array (
                'session' => $data->id,


                'module_name' => 'Meetings',


                'name_value_list' => array( array (
                    "name" => "name",
                    "value" => "Subject" 
            ),
            array (
                    "name" => "description",
                    "value" => "description" 
            ),

            array (
                    "name" => "location",
                    "value" => "Pune" 
            ),

            array (
                    "name" => "duration_hours",
                    "value" =>"1" 
            ),
                ) );
 $dataMeeting = call ( "set_entry", $set_contact_parameters, $url );

 $parameters = array(
                'session' =>  $data->id,
                'module_name' => 'Meetings',
                'module_id' => $dataMeeting->id,
                'link_field_name' => 'contacts',
                'related_ids ' => array('25627846-a8a2-eeb5-3565-532035113842'),
 );

$dataContactMeetings = call ( "set_relationship", $parameters, $url );

*/

/*
{
 'assigned_user_id': '1', # My user's ID
 'date_end': '2013-04-16 01:30:00',
 'date_start': '2013-04-16 01:23:45',
 'description': 'hello world',
 'location': 'JCenter',
 'name': 'Test',
 'team_id': '1',
 'type': 'Sugar' # This doesn't seem to be required
}

Also needs to set user_id so it appears in user calendar?
*/
