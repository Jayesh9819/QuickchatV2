<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popup Notifications</title>
    <style>
        /* Add CSS styles for the popup notifications */
        .popup {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background-color: #fff;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            z-index: 9999;
        }
        .popup-buttons {
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <!-- Your website content -->

    <script>
    // Function to play notification sound
    function playNotificationSound() {
        console.log("Playing notification sound."); // Debugging
        let audio = new Audio('notification_sound.mp3');
        audio.play();
    }

    // Create EventSource to listen for notifications
    console.log("Setting up EventSource for notifications."); // Debugging
    let eventSource = new EventSource('./Public/Popup/popup.php');

    // Event listener for receiving notifications
    eventSource.onmessage = function(event) {
        console.log("Received message: ", event.data); // Debugging
        let notification = event.data;  // Assuming each message is a single notification

        // Create and display popup notification
        console.log("Displaying notification: ", notification); // Debugging
        if (notification) {
            // Create popup element
            let popup = document.createElement('div');
            popup.classList.add('popup');
            popup.textContent = notification;

            // Create close button
            let closeButton = document.createElement('button');
            closeButton.textContent = 'Close';
            closeButton.addEventListener('click', () => {
                console.log("Closing notification."); // Debugging
                popup.remove();
            });

            // Create view button
            let viewButton = document.createElement('button');
            viewButton.textContent = 'View';
            viewButton.addEventListener('click', () => {
                console.log("View button clicked."); // Debugging
                // Handle view button click
            });

            // Append buttons to a div
            let buttonContainer = document.createElement('div');
            buttonContainer.classList.add('popup-buttons');
            buttonContainer.appendChild(viewButton);
            buttonContainer.appendChild(closeButton);

            // Append buttons to the popup
            popup.appendChild(buttonContainer);

            // Append popup to the document body
            document.body.appendChild(popup);

            // Play notification sound
            playNotificationSound();
        }
    };

    eventSource.onerror = function(event) {
        console.error("EventSource encountered an error: ", event); // Debugging
        console.log("ReadyState: ", this.readyState);
        if (this.readyState == EventSource.CONNECTING) {
            console.log("Attempting to reconnect...");
        } else if (this.readyState == EventSource.CLOSED) {
            console.log("Connection was closed.");
        }
    };
</script>

</body>
</html>
