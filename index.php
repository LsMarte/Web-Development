<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = strip_tags(trim($_POST['name']));
    $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
    $subject = strip_tags(trim($_POST['subject']));
    $message = strip_tags(trim($_POST['message']));

    // Validation
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        http_response_code(400);
        echo json_encode(['message' => 'Please fill out all fields.']);
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(['message' => 'Please enter a valid email address.']);
        exit;
    }

    // Email configuration (adjust these values)
    $recipient = "Roandy1017@gmail.com";
    $email_subject = "New contact from $name: $subject";
    $email_body = "Name: $name\n";
    $email_body .= "Email: $email\n\n";
    $email_body .= "Subject: $subject\n\n";
    $email_body .= "Message:\n$message\n";
    $headers = "From: $email";

    // Send email
    if (mail($recipient, $email_subject, $email_body, $headers)) {
        http_response_code(200);
        echo json_encode(['message' => 'Thank you! Your message has been sent.']);
    } else {
        http_response_code(500);
        echo json_encode(['message' => 'Oops! Something went wrong and we couldn\'t send your message.']);
    }
} else {
    http_response_code(403);
    echo json_encode(['message' => 'There was a problem with your submission, please try again.']);
}
?>
