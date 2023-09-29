

jQuery(document).ready(function ($) {

    var orderData = {}
    const nameInput = $('#ptc_wc_order_name');
    const phoneInput = $('#ptc_wc_order_phone');
    const shippingAddressInput = $('#ptc_wc_shipping_address');
    const totalPriceDom = $('#ptc_wc_order_total_price');
    const orderItemsDom = $('#ptc_wc_order_items');
    const orderTotalItemsDom = $('#ptc_wc_total_order_items');

    $('.column-pathao').on('click', function (e) {
        e.preventDefault();
    });

    $('.open-modal-button').on('click', async function (e) {
        e.preventDefault();
        var orderID = $(this).data('order-id');
        $('#ptc_wc_order_number').val(orderID);  // Set the Order ID in a hidden field
        $('#custom-modal').show();
        clearModalData();
        getOrderInfoAndPopulateModalData(orderID);
    });

    $('.close').on('click', function () {
        $('#custom-modal').hide();
        orderData = {};
    });

    // Close the modal if clicked outside the modal content
    $('#custom-modal').on('click', function (e) {
        if ($(e.target).closest('.modal-content').length === 0) {
            $('#custom-modal').hide();
            orderData = {};
        }
    });

    getOrderInfoAndPopulateModalData = async function (orderID) {
        $.post(ajaxurl, {
            action: 'get_wc_order',
            order_id: orderID
        }, function (response) {
            orderData = response.data;
            populateModalData();
        });
    }

    populateModalData = async function () {
        if (orderData) {
            nameInput.val(orderData?.billing?.full_name);
            phoneInput.val(orderData?.billing?.phone);
            shippingAddressInput.val(`${orderData?.shipping?.address_1}, ${orderData?.shipping?.address_2}, ${orderData?.shipping?.city}, ${orderData?.shipping?.state}, ${orderData?.shipping?.postcode}`);

            totalPriceDom.html(`${orderData.total} ${orderData.currency}`);

            let orderItems = '';

            orderData?.items?.forEach(function (item) {
                orderItems += `
                <li> 
                    <img width="40px" src="${item.image}" /> 
                    ${item.name}, 
                    Price: ${item.price} ${orderData.currency}, 
                    Quantity: ${item.quantity}  
                    <a href="${item.product_url}">Detail</a>
                </li>`;
            });

            orderTotalItemsDom.html(orderData?.total_items);

            orderItemsDom.html(orderItems);
        } 

    }

    clearModalData = function () {
        nameInput.val('');
        phoneInput.val('');
        shippingAddressInput.val('');
        totalPriceDom.html('');
        orderItemsDom.html('');
        orderTotalItemsDom.html('');
    }

});


jQuery(document).ready(function ($) {
    $('#city').change(function () {
        $('#zone').html('<option value="">Select Zone</option>');
        $('#area').html('<option value="">Select Area</option>');
        const city_id = $(this).val();
        $.post(ajaxurl, {
            action: 'get_zones',
            city_id: city_id
        }, function (response) {
            const zones = response.data.data.data;
            let options = '<option value="">Select Zone</option>';
            zones.forEach(function (zone) {
                options += `<option value="${zone.zone_id}">${zone.zone_name}</option>`;
            });
            $('#zone').html(options);
        });
    });

    $('#zone').change(function () {
        $('#area').html('<option value="">Select Area</option>');

        const zone_id = $(this).val();
        $.post(ajaxurl, {
            action: 'get_areas',
            zone_id: zone_id
        }, function (response) {
            const areas = response.data.data.data;
            let options = '<option value="">Select Area</option>';
            areas.forEach(function (area) {
                options += `<option value="${area.area_id}">${area.area_name}</option>`;
            });
            $('#area').html(options);
        });
    });
});

jQuery(document).ready(function ($) {

    // Initialize click event for submit button
    $("#submit-button").on("click", function (e) {
        // Gather form data
        const orderData = {
            merchant_order_id: $("#order_number").val(),
            sender_name: $("#name").val(),
            sender_phone: $("#phone").val().replace('+88'),
            recipient_name: $("#name").val(),
            recipient_phone: $("#phone").val().replace('+88'),
            recipient_address: $("#address").val(),
            recipient_city: $("#city").val(),
            recipient_zone: $("#zone").val(),
            recipient_area: $("#area").val(),
            amount_to_collect: $("#price").val(),
            store_id: $("#store").val(),
            delivery_type: 48, // Replace with actual delivery type
            item_type: 2, // Replace with actual item type
            item_quantity: 1,
            item_weight: 1,
            item_description: "Description" // Replace
        };

        // Perform AJAX request
        $.ajax({
            url: ajaxurl,  // Replace `ajaxurl` with the URL to the WordPress AJAX handler, usually 'admin-ajax.php'
            type: "POST",
            data: {
                action: "create_order",  // This should match the action hook in WordPress
                order_data: orderData
            },
            success: function (response) {
                if (response.success) {
                    alert("Order successfully created!");
                } else {
                    alert("Error: " + response.data);
                }
            },
            error: function () {
                alert("Something went wrong!");
            }
        });
    });
});
