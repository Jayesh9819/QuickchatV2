<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Popup Notifications</title>
    <style>
        .popup {
            position: fixed;
            top: 10%;
            left: 50%;
            transform: translateX(-50%);
            background-color: #f8f8f8;
            border: 1px solid #ccc;
            padding: 20px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.2);
            width: 300px;
            margin-top: 10px;
            z-index: 10000;
        }
        .popup-buttons {
            text-align: right;
            margin-top: 10px;
        }
        button {
            padding: 5px 10px;
            margin-left: 5px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            opacity: 0.8;
        }
        #closeAll {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 10px 20px;
            background-color: #dc3545;
            color: white;
            font-size: 16px;
            border-radius: 5px;
            cursor: pointer;
            z-index: 10001;
        }
    </style>
</head>
<body>
    <button id="closeAll">Close All</button>
    <!-- Your website content -->

    <script>
        function playNotificationSound() {
            let audio = new Audio('notification_sound.mp3');
            audio.play();
        }

        let eventSource = new EventSource('../Public/Popup/bpop.php');

        let popupContainer = document.createElement('div');
        popupContainer.style.position = 'fixed';
        popupContainer.style.top = '50px';
        popupContainer.style.left = '50%';
        popupContainer.style.transform = 'translateX(-50%)';
        popupContainer.style.width = '320px';
        document.body.appendChild(popupContainer);

        eventSource.onmessage = function(event) {
            let notification = event.data.trim(); // Trim any extra whitespace
            console.log("Received notification:", notification); // Log the trimmed notification

            // Check the notification text case-insensitively
            if (notification.toLowerCase() !== "no new transactions") {
                let popup = document.createElement('div');
                popup.classList.add('popup');
                popup.textContent = notification;

                let closeButton = document.createElement('button');
                closeButton.textContent = 'Close';
                closeButton.addEventListener('click', function() {
                    popupContainer.removeChild(popup);
                });

                let viewButton = document.createElement('button');
                viewButton.textContent = 'View';
                viewButton.addEventListener('click', function() {
                    // Handle view button click
                });

                let buttonContainer = document.createElement('div');
                buttonContainer.classList.add('popup-buttons');
                buttonContainer.appendChild(viewButton);
                buttonContainer.appendChild(closeButton);

                popup.appendChild(buttonContainer);
                popupContainer.appendChild(popup);
                playNotificationSound();
            } else {
                console.log("Filtered out message: 'No new transactions'");
            }
        };

        eventSource.onerror = function(event) {
            console.error("EventSource encountered an error: ", event);
        };

        document.getElementById('closeAll').addEventListener('click', function() {
            while (popupContainer.firstChild) {
                popupContainer.removeChild(popupContainer.firstChild);
            }
        });
    </script>
</body>
</html>
