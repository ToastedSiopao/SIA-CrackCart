document.addEventListener('DOMContentLoaded', function() {
    // Event delegation for incident decision buttons
    document.body.addEventListener('click', function(event) {
        if (event.target.matches('.incident-decision-btn')) {
            const decision = event.target.dataset.decision;
            const orderId = event.target.dataset.orderId;
            const notificationId = event.target.dataset.notificationId;

            handleIncidentDecision(decision, orderId, notificationId);
        }
    });
});

function handleIncidentDecision(decision, orderId, notificationId) {
    if (!confirm(`Are you sure you want to ${decision} this order?`)) {
        return;
    }

    fetch('api/handle_incident_response.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            order_id: orderId,
            decision: decision,
            notification_id: notificationId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'success') {
            alert('Your decision has been recorded.');
            // Optionally, refresh notifications or update UI
            location.reload(); 
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An unexpected error occurred.');
    });
}
