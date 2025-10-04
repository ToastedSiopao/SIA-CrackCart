<?php

if (!isset($_SESSION['shipment'])) {
    $_SESSION['shipment'] = array();
}

function addToShipment($service_tier, $quantity, $price) {
    if (isset($_SESSION['shipment'][$service_tier])) {
        $_SESSION['shipment'][$service_tier]['quantity'] += $quantity;
    } else {
        $_SESSION['shipment'][$service_tier] = array(
            'quantity' => $quantity,
            'price' => $price
        );
    }
}

function updateShipment($service_tier, $quantity) {
    if (isset($_SESSION['shipment'][$service_tier])) {
        if ($quantity > 0) {
            $_SESSION['shipment'][$service_tier]['quantity'] = $quantity;
        } else {
            removeFromShipment($service_tier);
        }
    }
}

function removeFromShipment($service_tier) {
    if (isset($_SESSION['shipment'][$service_tier])) {
        unset($_SESSION['shipment'][$service_tier]);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_to_shipment'])) {
        $service_tier = $_POST['service_tier'];
        $quantity = $_POST['quantity'];
        $price = $_POST['price'];
        addToShipment($service_tier, $quantity, $price);
    } elseif (isset($_POST['update_shipment'])) {
        $service_tier = $_POST['service_tier'];
        $quantity = $_POST['quantity'];
        updateShipment($service_tier, $quantity);
    } elseif (isset($_POST['remove_from_shipment'])) {
        $service_tier = $_POST['service_tier'];
        removeFromShipment($service_tier);
    }
    header('Location: view_shipment.php');
    exit;
}
?>