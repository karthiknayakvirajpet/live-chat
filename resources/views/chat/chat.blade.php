@extends('base')

<!-- CSS STYLE -->
<style type="text/css">
    .card {
            height: 80%;
        }

    @media (max-width: 767px) {
        .card {
            height: 80%; /* Adjust the height for mobile view */
        }
    }
</style>


@section('content')
<div class="container">
    <div class="d-flex justify-content-center align-items-center" style="height: 85vh;">
        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <div class="row">
                        @if(auth()->user()->role != 1)
                            <div class="col-md-7">
                                Messages
                            </div>
                            <div class="col-md-4">
                                Support Agent Status: 
                                @if($support_agent->active == 1)
                                    <i class='fa fa-circle' title='de-active' style='color: #4ff339'></i>
                                    Online
                                @else
                                    <i class='fa fa-circle' title='active' style='color: red'></i>
                                    Offline
                                @endif
                            </div>

                            <div class="col-md-1">
                                <button type="button" rel="tooltip" class="btn btn-danger" data-original-title="" title="Clear Chat" name="clearchat" value="{{ auth()->user()->id }}">
                                    <i class="fa fa-trash" aria-hidden="true"></i>
                                </button>
                            </div>
                        @else
                            <div class="col-md-9">
                                Messages | Cutomer Name: {{ $user_chat_name->name }}
                            </div>
                            <div class="col-md-3">
                                Update Status:
                                {{-- 
                                @if(auth()->user()->active)
                                    Online
                                @else
                                    Offline
                                @endif
                                --}}
                                <label class="switch">a
                                    <input type="checkbox" class="active-switch" data-user-id="{{ auth()->user()->id }}" {{ auth()->user()->active ? 'checked' : '' }}>
                                    <span class="slider round"></span>
                                </label>
                            </div>
                        @endif
                    </div>
                </div>
            
                <div class="chat-messages" id="message-list">

                </div>

                <div class="chat-input">
                    <input type="text" id="message-input" placeholder="Message">
                    <button id="send-button" onclick="sendMessage()">Send</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection



<!-- SCRIPTS -->
<script src="https://code.jquery.com/jquery-3.4.0.min.js"></script>
<script type="text/javascript">
$(document).ready(function()
{
    //***************************************************************
    //message send function
    //***************************************************************
    function sendMessage() {
        const messageInput = document.getElementById('message-input');
        const message = messageInput.value.trim();

        if (message === '') {
            return;
        }

        $.ajax({
            url: '/chat-send', //route to send messages
            type: 'POST',
            data: { "_token": "{{ csrf_token() }}", message: message, user_chat_id: '{{ $user_chat_id }}' }, 
            success: function(response) {

                //addMessage('123', message);

                //go to end of the message box after message sent
                const messageList = document.getElementById('message-list');
                messageList.scrollTop = messageList.scrollHeight;
            },
            error: function(error) {
                console.log('Failed to send message');
            }
        });
        messageInput.value = '';
    }


    //***************************************************************
    //Fetch messages on page load
    //***************************************************************
    $.ajax({
        url: '/chat/messages/' + '{{ $user_chat_id }}', //route to get messages
        type: 'GET',
        success: function(response) {
            // `response` contains the messages in JSON format
            const messages = response.messages;

            const messageList = document.getElementById('message-list');
            messageList.innerHTML = '';

            messages.forEach((message) => {
                const messageDiv = document.createElement('div');
                messageDiv.classList.add('chat-message');

                const dateObject = new Date(message.created_at);

                // Add 5 hours and 30 minutes to the date object
                dateObject.setHours(dateObject.getHours() + 5);
                dateObject.setMinutes(dateObject.getMinutes() + 30);

                // Get the updated date and time in "yyyy-mm-ddThh:mm:ss.sssZ" format
                //const updatedCreatedAt = dateObject.toISOString();

                // Extract the date in "yyyy-mm-dd" format
                const year = dateObject.getUTCFullYear();
                const month = String(dateObject.getUTCMonth() + 1).padStart(2, '0');
                const day = String(dateObject.getUTCDate()).padStart(2, '0');
                const formattedDate = `${year}-${month}-${day}`;

                // Extract the time in "hh:mm:ss" format
                const hours = String(dateObject.getUTCHours()).padStart(2, '0');
                const minutes = String(dateObject.getUTCMinutes()).padStart(2, '0');
                const seconds = String(dateObject.getUTCSeconds()).padStart(2, '0');
                const formattedTime = `${hours}:${minutes}:${seconds}`;


                //message alignment based on sender
                var logged_user_id = '{{ auth()->user()->id }}';
                if(logged_user_id == message.sender_id)
                {
                    messageDiv.style.textAlign = 'right';
                    messageDiv.style.borderRadius = '10px';
                }

                messageDiv.innerHTML = `
                    <span class="user">${message.name}:</span>
                    <span class="message">
                        ${message.message}
                        <span class="date" style="font-size: 9px;">
                            ${formattedDate +' '+ formattedTime}
                        </span>
                    </span>
                `;
                messageList.appendChild(messageDiv);

                // Scroll to the end after all messages are loaded
                messageList.scrollTop = messageList.scrollHeight;
            });
        },
        error: function(error) {
            console.error('Failed to fetch messages:', error);
        }
    });


    //***************************************************************
    //Add latest sent message to the chatbox
    //***************************************************************
    function addMessage(user, message) 
    {
        const messageList = document.getElementById('message-list');
        const messageDiv = document.createElement('div');

        // Get the current date
        const currentDate = new Date();

        // Extract the year, month, and day from the date object
        const year = currentDate.getFullYear();
        const month = String(currentDate.getMonth() + 1).padStart(2, '0'); // Months are zero-indexed, so add 1 to get the correct month
        const day = String(currentDate.getDate()).padStart(2, '0');

        // Extract the time in "hh:mm:ss" format
        const hours = String(currentDate.getHours()).padStart(2, '0');
        const minutes = String(currentDate.getMinutes()).padStart(2, '0');
        const seconds = String(currentDate.getSeconds()).padStart(2, '0');
        const formattedTime = `${hours}:${minutes}:${seconds}`;

        // Concatenate the components to form the "yyyy-mm-dd" date string
        const formattedDate = `${year}-${month}-${day}`;

        messageDiv.classList.add('chat-message');
        messageDiv.innerHTML = `
            <span class="user">{{auth()->user()->name}}:</span>
            <span class="message">
                ${message}
                <span class="date" style="font-size: 9px;">
                    ${formattedDate +' '+ formattedTime}
                </span>
            </span>
        `;
        messageList.appendChild(messageDiv);

        // Scroll to the end after all messages are loaded
        messageList.scrollTop = messageList.scrollHeight;
    }


    //***************************************************************
    //Send Button click event
    //***************************************************************
    document.getElementById('send-button').addEventListener('click', sendMessage);


    //***************************************************************
    // Add event listener to input field to detect "Enter" key press
    //***************************************************************
    document.getElementById('message-input').addEventListener('keypress', function (event) {
        if (event.key === 'Enter') {
            sendMessage(); // Call the sendMessage function when Enter key is pressed
            event.preventDefault(); // Prevent the default behavior of the Enter key (e.g., line break in textarea)
        }
    });



    //***************************************************************
    //Clear chat history
    //***************************************************************
    $('[name=clearchat]').click(function (){
        var customer_id = $(this).val();
        
        swal("Are you sure you want to clear the chat?", {
          dangerMode: true,
          buttons: true,
        }).then((Delete) => 
        {
            if (Delete)
            {
                $.ajax({
                      url: "/chat/clear/" + customer_id,
                      type: 'GET',
                      success: function(){
                          swal({
                            title: "Chat cleared successfully!",
                          }).then(function(){ 
                              location.reload();
                             }
                          );
                      }
                });
            }    
        }).catch(swal.noop);
    });


    //***************************************************************
    //Change user active status of switch toggle button
    //***************************************************************
    $('.active-switch').on('change', function() {
      const userId = $(this).data('user-id');
      const isActive = $(this).prop('checked') ? 1 : 0; // Convert true to 1 and false to 0

      // Send an AJAX request to update the active status
      $.ajax({
        method: 'POST',
        url: '/update-status',
        data: { "_token": "{{ csrf_token() }}", user_id: userId, is_active: isActive, },
        success: function(response) {
          console.log(response);
        },
        error: function(error) {
          console.error(error);
        }
      });
    });

});
</script>



<!--
//***************************************************************
// Pusher Functionality - Broacast messages
//***************************************************************
-->
<script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>

// Pusher.logToConsole = true;

var app_key = '{{ env('PUSHER_APP_KEY') }}';
var app_cluster = '{{ env('PUSHER_APP_CLUSTER') }}';

// Configure Pusher with your credentials
var pusher = new Pusher(app_key, {
        cluster: app_cluster,
        encrypted: true
    });

// Subscribe to the 'chat' channel
var channel = pusher.subscribe('chat');

//Get event data
channel.bind('message', function (data) 
{
    //console.log(data);

    // Get the current date
    const currentDate = new Date();

    // Extract the year, month, and day from the date object
    const year = currentDate.getFullYear();
    const month = String(currentDate.getMonth() + 1).padStart(2, '0'); // Months are zero-indexed, so add 1 to get the correct month
    const day = String(currentDate.getDate()).padStart(2, '0');

    // Extract the time in "hh:mm:ss" format
    const hours = String(currentDate.getHours()).padStart(2, '0');
    const minutes = String(currentDate.getMinutes()).padStart(2, '0');
    const seconds = String(currentDate.getSeconds()).padStart(2, '0');
    const formattedTime = `${hours}:${minutes}:${seconds}`;

    // Concatenate the components to form the "yyyy-mm-dd" date string
    const formattedDate = `${year}-${month}-${day}`;

            
    // `data` contains the new message sent from the server
    const messageList = document.getElementById('message-list');
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('chat-message');

    
    var logged_user_id = '{{ auth()->user()->id }}';
    var user_role = '{{ auth()->user()->role }}';
    var user_chat_id = '{{ $user_chat_id }}';

    // console.log('logged' + logged_user_id);
    // console.log('sender' + data.sender_user_id);
    // console.log('receiver' + data.receiver_id);
    // console.log('user_chat_id' + user_chat_id);

    //message alignment based on sender
    if(logged_user_id == data.sender_user_id)
    {
        messageDiv.style.textAlign = 'right';
        messageDiv.style.borderRadius = '10px';
    }

    //Show messages based on customer dynamically
    if((logged_user_id == data.sender_user_id && data.receiver_id == 1) || (logged_user_id == 1 && data.sender_user_id == user_chat_id) || (logged_user_id == data.receiver_id && data.sender_user_id == 1) || (logged_user_id == 1 && data.receiver_id == user_chat_id))
    {
        messageDiv.innerHTML = `
            <span class="user">${data.sender_name}:</span>
            <span class="message">
                ${data.message} 
                <span class="date" style="font-size: 9px;">
                    ${formattedDate +' '+ formattedTime}
                </span>
            </span>
        `;
        messageList.appendChild(messageDiv);
    }

    //go to end of the message box after message sent
    messageList.scrollTop = messageList.scrollHeight;
});




//***************************************************************
// Show Notification
//***************************************************************
// Subscribe to the 'show-notification' channel
var new_message = pusher.subscribe('show-notification');

var logged_user_id = '{{ auth()->user()->id }}';
var user_chat_id = '{{ $user_chat_id }}';

//Get event data
new_message.bind('new-message', function (data) 
{
    // Display a notification to the receiver when a new message is received
    if((logged_user_id == data.sender_user_id && data.receiver_id == 1) || (logged_user_id == 1 && data.sender_user_id == user_chat_id) || (logged_user_id == data.receiver_id && data.sender_user_id == 1) || (logged_user_id == 1 && data.receiver_id == user_chat_id))
    {
        showNotification(data.message, data.sender_name);
    }
});

//***************************************************************
// Function to show a browser notification
//***************************************************************
function showNotification(message, sender_name) {
    // Check if the browser supports notifications
    if (!('Notification' in window)) {
        console.log('This browser does not support notifications.');
        return;
    }

    // Request permission to show notifications (if not already granted)
    if (Notification.permission !== 'granted') {
        Notification.requestPermission().then(function (permission) {
            if (permission === 'granted') {
                createNotification(message, sender_name);
            }
        });
    } else {
        createNotification(message, sender_name);
    }
}

//***************************************************************
// Function to create the notification
//***************************************************************
function createNotification(message, sender_name) {
    const notification = new Notification('New Message from ' + sender_name, {
        body: message,
        icon: "{{ asset('images/notification-icon.png') }}",
    });


    // notification.onclick = function () {
    //     //
    // };

    // notification.onclose = function () {
    //     //
    // };
}


</script>