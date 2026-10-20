<?php
require_once '../config/database.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

requireRole('student');

$studentId = $_SESSION['user_id'];
$orderId = filter_input(INPUT_GET, 'order_id', FILTER_VALIDATE_INT);

if (!$orderId) {
    redirect('orders.php');
}

$stmt = $pdo->prepare("
    SELECT o.id, o.status,s.title AS service_title,
        u.name AS customer_name
    FROM orders o
    INNER JOIN services s
        ON s.id = o.service_id
    INNER JOIN users u
        ON u.id = o.customer_id
    WHERE o.id = ?
    AND o.student_id = ?
");

$stmt->execute([$orderId, $studentId]);
$order = $stmt->fetch();

if (!$order) {
    redirect('orders.php');
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Messages - DiskarDesk</title>
    <link rel="icon" href="../public/logo/Logo_trns.png" type="image/x-icon">
    <link href="../public/css/framework/bootstrap.min.css" rel="stylesheet">
</head>

<body>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
    </div>

    <div class="row justify-content-center">

        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body">
                    <h3>Messages</h3>
                    <p class="text-muted">Service: <?= e($order['service_title']) ?></p>
                    <p>Customer: <strong><?= e($order['customer_name']) ?></strong></p>
                    <hr>

                    <div id="messages" class="border rounded p-3 mb-3"style="height: 350px; overflow-y: auto;">
                        Loading messages...
                    </div>

                    <form id="messageForm">
                        <input type="hidden" id="csrf_token" value="<?= e(csrfToken()) ?>">
                        <input type="hidden" id="order_id" value="<?= (int) $orderId ?>">
                        <div class="input-group">
                            <input type="text" id="message" class="form-control"
                                placeholder="Type your message..." autocomplete="off">
                            <button type="submit" class="btn btn-primary">Send</button>
                        </div>
                    </form>

                    <div id="messageError" class="text-danger mt-2"></div>
                </div>
            </div>
        </div>

    </div>

</div>

    <script src="../public/js/framework/jquery-3.6.0.min.js"></script>
    <script>
    function loadMessages() {

        const orderId = $('#order_id').val();

        $.ajax({
            url: '../ajax/get-messages.php',
            type: 'GET',
            data: {
                order_id: orderId
            },

            dataType: 'json',
            success: function(response) {

                if (!response.success) {
                    return;
                }

                let html = '';
                response.messages.forEach(function(message) {
                    html += `
                        <div class="mb-3">
                            <strong>${escapeHtml(message.sender_name)}</strong>
                            <div>${escapeHtml(message.message)}</div>
                            <small class="text-muted">${escapeHtml(message.created_at)}</small>
                        </div>
                    `;

                });

                if (html === '') {
                    html = `<p class="text-muted">No messages yet.</p>`;
                }

                $('#messages').html(html);
                $('#messages').scrollTop(
                    $('#messages')[0].scrollHeight
                );
            }
        });
    }


    function escapeHtml(text) {
        return $('<div>').text(text).html();
    }


    $('#messageForm').on('submit', function(e) {

        e.preventDefault();
        const orderId = $('#order_id').val();
        const message = $('#message').val().trim();

        if (message === '') {
            return;
        }

        $.ajax({
            url: '../ajax/send-message.php',
            type: 'POST',
            data: {
                order_id: orderId,
                message: message,
                csrf_token: $('#csrf_token').val()
            },

            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    $('#message').val('');
                    loadMessages();
                    $('#messageError').html('');
                } else {
                    $('#messageError').text(response.message);
                }
            },
            error: function() {
                $('#messageError').text('Unable to send message.');
            }
        });

    });


    loadMessages();
    setInterval(loadMessages,5000);
    </script>

</body>

</html>