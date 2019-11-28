<?php

class ksf_crm_contacts
{
	var $id;
	var $person_id;				//crm_contacts
	var $type;				//crm_contacts
	var $action;				//crm_contacts
	var $entity_id;				//crm_contacts
	var $id_crm_campaign_status_code;	//crm_person_details
	var $website_url;			//crm_person_details, dotproject, vtiger
	var $salutation;			//crm_person_details
	var $job_title;				//crm_person_details
	var $department;			//crm_person_details
	var $homephone;				//crm_person_details
	var $notes;				//crm_person_details
	var $inactive;				//crm_person_details
	var $became_customer_date;		//crm_person_details
	var $id_users;				//crm_person_details
	//var $id_crm_person;			//crm_person_details
	var $gender;				//crm_person_details
	var $ref;				//crm_persons
	var $name;				//crm_persons
	var $name2;				//crm_persons
	var $address;				//crm_persons, dotproject, vtiger
	var $address_2;				//ospos_people, dotproject, vtiger
	var $city;				//ospos_people, dotproject, vtiger
	var $state;				//ospos_people, dotproject, vtiger
	var $zip;				//ospos_people, dotproject, vtiger
	var $country;				//ospos_people, dotproject, vtiger
	var $phone;				//crm_persons, dotproject, vtiger
	var $phone2;				//crm_persons, dotproject, vtiger
	var $mobile;				//vCard, dotproject, vtiger
	var $fax;				//crm_persons, dotproject, vtiger
	var $email;				//crm_persons
	var $email2;				//dotproject, vtiger
	var $lang;				//crm_persons, vCard
	var $notes;				//crm_persons
	var $inactive;				//crm_persons
	var $user_login;			//wp_users
	var $user_pass;				//wp_users
	var $user_nicename;			//wp_users
	var $user_registered;			//wp_users
	var $user_activation_key;		//wp_users
	var $user_status;			//wp_users
	var $display_name;			//wp_users
	var $taxable;				//ospos_customers
	var $account_number;			//ospos_customers
	var $birthdate;				//vCard
	var $company_name;			//vCard, dotproject, vtiger
	var $contact_type;			//dotproject
	var $contact_icq = NULL;		//dotproject
	var $contact_aol = NULL;		//dotproject
	var $contact_yahoo = NULL;		//dotproject
	var $contact_msn = NULL;		//dotproject
	var $contact_jabber = NULL;		//dotproject
	var $contact_owner;			//dotproject
	var $data_dictionary = array();			//For translating our fields to the other entities fields
	var $assistant;				//vCard
	var $manager;				//vCard
	var $spouse;				//vCard
	var $contact_aim;			//vCard
	var $gtalk;				//vCard
	var $twitter;				//vCard
	var $facebook;
	var $skype;				//vCard
	var $ms_imaddress;			//vCard
	var $groupwise;				//vCard
	var $ms_cardpicture;			//vCard
	var $photo;				//vCard
	var $mozzilla_html;			//vCard		T/F prefers HTML mail
	var $blogurl;				//vCard
	var $birthplace;			//vCard
	var $deathdate;				//vCard
	var $deathplace;			//vCard
	var $expertise;				//vCard
	var $hobby;				//vCard
	var $interest;				//vCard	rec activity interested in but not necessarily active
	var $agent;				//vCard someone who can act on behalf of vCard info

	function __construct()
	{
		$this->data_dictionary['dotproject'] = array( array( 'our_field' => 'contact_owner', 'their_field' => 'contact_owner' ),
		       						array( 'our_field' => 'contact_jabber', 'their_field' => 'contact_jabber' ),	)
	}
	function import_external( $ext_class, $ext_class_name )
	{
		//Use data_dictionary, the class name, and list of fields
		//go through list of fields and check in ext_class to
		//copy to our class
	}
	function export_external( $ext_class, $ext_class_name )
	{
		//Use data_dictionary, the class name, and list of fields
		//go through list of fields and check in this class to
		//copy to their class
	}

