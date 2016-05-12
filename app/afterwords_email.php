<?php
if ($_POST) {
    $to_email = "afterwordstranscription@gmail.com"; //Recipient email
    $from_email = "transcriptsauthorized@gmail.com"; //From email address

    //check if its an ajax request, exit if not
    if (!isset($_SERVER['HTTP_X_REQUESTED_WITH']) AND strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) != 'xmlhttprequest') {
        $output = json_encode(array( //create JSON data
            'type' => 'error',
            'text' => 'Sorry Request must be Ajax POST'
        ));
        die($output); //exit script outputting json data
    }

    //Sanitize input data using PHP filter_var().
    $clientName = filter_var($_POST["clientName"], FILTER_SANITIZE_STRING);
    $clientEmail = filter_var($_POST["clientEmail"], FILTER_SANITIZE_EMAIL);
    $clientAddress = filter_var($_POST["clientAddress"], FILTER_SANITIZE_STRING);
    $subject = filter_var($_POST["emailSubject"], FILTER_SANITIZE_STRING);
    $message = filter_var($_POST["emailBody"], FILTER_SANITIZE_STRING);

    //additional php validation
    if (strlen($clientName) < 3) { // If length is less than 4 it will output JSON error.
        $output = json_encode(array('type' => 'error', 'text' => 'Name is too short or empty!'));
        die($output);
    }
    if (!filter_var($clientEmail, FILTER_VALIDATE_EMAIL)) { //email validation
        $output = json_encode(array('type' => 'error', 'text' => 'Please enter a valid email!'));
        die($output);
    }
    if (strlen($clientAddress) < 3) { //check emtpy address
        $output = json_encode(array('type' => 'error', 'text' => 'Address is required'));
        die($output);
    }
    if (strlen($subject) < 3) { //check emtpy subject
        $output = json_encode(array('type' => 'error', 'text' => 'Subject is required'));
        die($output);
    }
    if (strlen($message) < 3) { //check emtpy message
        $output = json_encode(array('type' => 'error', 'text' => 'Too short message! Please enter something.'));
        die($output);
    }

    //email body
    $message_body = $message . "\n\n" . $clientName . "\nEmail : " . $clientEmail . "\nAddress: " . $clientAddress;

    ### Attachment Preparation ###
    $file_attached = false;
    if (isset($_FILES['file_attach'])) //check uploaded file
    {
        //get file details we need
        $file_tmp_name = $_FILES['file_attach']['tmp_name'];
        $file_name = $_FILES['file_attach']['name'];
        $file_size = $_FILES['file_attach']['size'];
        $file_type = $_FILES['file_attach']['type'];
        $file_error = $_FILES['file_attach']['error'];

        //exit script and output error if we encounter any
        if ($file_error > 0) {
            $mymsg = array(
                1 => "The uploaded file exceeds the upload_max_filesize directive in php.ini",
                2 => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form",
                3 => "The uploaded file was only partially uploaded",
                4 => "No file was uploaded",
                6 => "Missing a temporary folder");

            $output = json_encode(array('type' => 'error', 'text' => $mymsg[$file_error]));
            die($output);
        }

        //read from the uploaded file & base64_encode content for the mail
        $handle = fopen($file_tmp_name, "r");
        $content = fread($handle, $file_size);
        fclose($handle);
        $encoded_content = chunk_split(base64_encode($content));
        //now we know we have the file for attachment, set $file_attached to true
        $file_attached = true;
    }


    if ($file_attached) //continue if we have the file
    {
        $boundary = md5(rand());

        //header
        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "From:" . $from_email . "\r\n";
        $headers .= "Reply-To: " . $clientEmail . "" . "\r\n";
        $headers .= "Content-Type: multipart/mixed; boundary = $boundary\r\n\r\n";

        //plain text
        $body = "--$boundary\r\n";
        $body .= "Content-Type: text/plain; charset=ISO-8859-1\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n\r\n";
        $body .= chunk_split(base64_encode($message_body));

        //attachment
        $body .= "--$boundary\r\n";
        $body .= "Content-Type: $file_type; name=\"$file_name\"\r\n";
        $body .= "Content-Disposition: attachment; filename=\"$file_name\"\r\n";
        $body .= "Content-Transfer-Encoding: base64\r\n";
        $body .= "X-Attachment-Id: " . rand(1000, 99999) . "\r\n\r\n";
        $body .= $encoded_content;

    } else {
        //proceed with PHP email.
        $headers = "From:" . $from_email . "\r\n" .
            'Reply-To: ' . $clientEmail . '' . "\n" .
            'X-Mailer: PHP/' . phpversion();
        $body = $message_body;
    }

    $send_mail = mail($to_email, $subject, $body, $headers);


    if (!$send_mail) {
        //If mail couldn't be sent output error. Check your PHP email configuration (if it ever happens)
        $output = json_encode(array('type' => 'error', 'text' => 'Could not send mail! Please check your PHP mail configuration.'));
        die($output);
    } else {
        $output = json_encode(array('type' => 'message', 'text' => 'Hi ' . $clientName . ' Thank you for your email'));
        die($output);
    }
}
