<?php

function getConversation($user_id, $conn)
{
  // SQL query to get all conversations for the current user, including the unread message count
  $sql = "SELECT user_1, user_2, MAX(created_at) as last_message_time, 
                 SUM(CASE WHEN to_id = ? AND opened = 0 THEN 1 ELSE 0 END) as unread_messages
          FROM (
              SELECT CASE WHEN from_id = ? THEN to_id ELSE from_id END AS user_1,
                     CASE WHEN to_id = ? THEN from_id ELSE to_id END AS user_2,
                     created_at, to_id, opened
              FROM chats
              WHERE from_id = ? OR to_id = ?
          ) AS derived_table
          GROUP BY user_1, user_2
          ORDER BY last_message_time DESC";

  $stmt = $conn->prepare($sql);
  $stmt->execute([$user_id, $user_id, $user_id, $user_id, $user_id]);

  if ($stmt->rowCount() > 0) {
    $conversations = $stmt->fetchAll();
    $user_data = []; // Array to store user conversations including unread messages count

    foreach ($conversations as $conversation) {
      // Determine the other user's ID in the conversation
      $other_user_id = ($conversation['user_1'] == $user_id) ? $conversation['user_2'] : $conversation['user_1'];

      // Fetch the other user's details
      if (substr($other_user_id, 0, 2) === 'UT') {
        $sql2 = "SELECT * FROM unknown_users WHERE id = ?";
      } else {
        $sql2 = "SELECT * FROM user WHERE id = ?";
      }
      $stmt2 = $conn->prepare($sql2);
      $stmt2->execute([$other_user_id]);

      if ($stmt2->rowCount() > 0) {
        $otherUserDetails = $stmt2->fetch(); // Assuming you need just one row per user
        $otherUserDetails['unread_messages'] = $conversation['unread_messages'];
        array_push($user_data, $otherUserDetails);
      }
    }
    return $user_data;
  } else {
    return []; // No conversations found
  }
}


function getUnreadMessagesWithUserDetails($user_id, $conn)
{
  // SQL query to get all unread messages for the current user, including details of the user who sent the messages
  $sql = "SELECT u.id, u.name, u.email, COUNT(m.id) AS unread_messages
          FROM user u
          JOIN messages m ON u.id = m.from_id
          WHERE m.to_id = ? AND m.opened = 0 AND u.role = 'user'
          GROUP BY u.id, u.name, u.email
          ORDER BY unread_messages DESC";

  $stmt = $conn->prepare($sql);
  $stmt->execute([$user_id]);

  if ($stmt->rowCount() > 0) {
    $messages = $stmt->fetchAll();
    return $messages;
  } else {
    return []; // No unread messages found
  }
}
