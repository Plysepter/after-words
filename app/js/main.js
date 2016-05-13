/**
 * Created by brett on 12/05/16.
 */
function emailSubmit() {
    var email = {
        clientName: $('#nameOrCompany').val(),
        clientEmail: $('#emailAddress').val(),
        clientAddress: $('#clientAddress').val(),
        emailSubject: $('#emailSubject').val(),
        emailBody: $('#emailBody').val()
    };

    var subject = "Via website: " + email.emailSubject;
    var body = email.emailBody;

    var m_data = new FormData();
    m_data.append('clientName', email.clientName);
    m_data.append('clientEmail', email.clientEmail);
    m_data.append('clientAddress', email.clientAddress);
    m_data.append('emailSubject', subject);
    m_data.append('emailBody', body);
    m_data.append('file_attach', $('#attactmentUpload')[0].files[0]);

    var out = "Please wait...";
    $.ajax({
        url: 'afterwords_email.php',
        data: m_data,
        processData: false,
        contentType: false,
        type: 'POST',
        dataType: 'json',
        success: function (response) {
            //set success message
            $('#response').show().text("You send the stuff correctly this time");
        }
    });

}