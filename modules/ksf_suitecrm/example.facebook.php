https://github.com/facebook/facebook-php-sdk/tree/master/examples


/**********************************/
<?php
    $user = $facebook->getUser();

    if ($user) {
        $user_profile = $facebook->api('/me');
        $friends = $facebook->api('/me/friends');

        echo '<ul>';
        foreach ($friends["data"] as $value) {
            echo '<li>';
            echo '<div class="pic">';
            echo '<img src="https://graph.facebook.com/' . $value["id"] . '/picture"/>';
            echo '</div>';
            echo '<div class="picName">'.$value["name"].'</div>'; 
            echo '</li>';
        }
        echo '</ul>';
    }


/*************************/
<body>

<h1>Login to FaceBook using My Web Application</h1>

<div>
  <button id="login">Log In</button>
  <button id="logout">Log Out</button>

  <button id="disconnect">Disconnect</button>

</div>

<div id="user-info" style="display: none;"></div>

<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>

<div id="fb-root"></div>

<script src="http://connect.facebook.net/en_US/all.js"></script>

<script>
  // initialize the library with the API key
  FB.init({ apiKey: 'c2e2e62acc6a292c20c63404bcfecb23' });

  // fetch the status on load
  FB.getLoginStatus(handleSessionResponse);

  $('#login').bind('click', function() {
    FB.login(handleSessionResponse,{perms:'publish_stream,read_stream,offline_access,manage_pages,friends_activities,friends_about_me,user_hometown,user_birthday,user_about_me,user_hometown,user_photos,email,manage_friendlists,read_friendlists'});
  });

  $('#logout').bind('click', function() {
    FB.logout(handleSessionResponse);
  });

  $('#disconnect').bind('click', function() {
    FB.api({ method: 'Auth.revokeAuthorization' }, function(response) {
      clearDisplay();
    });
  });

  // no user, clear display
  function clearDisplay() {
    $('#user-info').hide('fast');
  }

  // handle a session response from any of the auth related calls
  function handleSessionResponse(response) {
    // if we dont have a session, just hide the user info
    if (!response.session) {
      clearDisplay();
      return;
    }

    FB.api(
      {
        method: 'fql.query',
      query: 'SELECT pic,hometown_location,name,username, uid, email, sex, birthday FROM user WHERE uid=' + FB.getSession().uid

      },
      function(response) {  
       var user = response[0];

       $('#user-info').html("<img src="+user.pic+"/>"+'<br/>ID: '+user.uid +'<br/>User Name: '+ user.name +'<br/>Email: '+ user.email +'<br/>Birth Day: '+ user.birthday +'<br/>Gender: ' + user.sex+'<br/>Home Town: ' +user.hometown_location['state']).show('fast');

      }
    );

    // if we have a session, query for the user's profile picture and name
    FB.api(
    {
     method: 'friends.get' 
    },
    function(response) 
    {

     $(response).each(function() {
     var userid = this;
     FB.api(
     {
      method: 'fql.query',
      query: 'SELECT uid,name,email,birthday FROM user WHERE uid=' + userid
    },function(response) 
    {
    var user = response[0]
     $('#user-info').append('<br/>ID: '+user.uid+ ', Name: '+user.name+' ,Email: '+user.email); 

    });
   });
    }
    );

    }

</script>

</body>

/**************************************/
You can get email hashes of user's friends via FQL (field "email_hashes" of table "user").

Another way to bind your users to facebook users - to use method "search" (it can search facebook users by email). I've used it when I had the same problem.

For example, this API call:

/search?q=pavel@vbnet.ru&type=user
returns:

{
   "data": [
      {
         "name": "Pavel Surmenok",
         "id": "100001653100014"
      }
   ]
}
You can iterate on your emails and search whether they registered on Facebook.

/**********************************/
