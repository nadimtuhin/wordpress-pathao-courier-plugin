

jQuery(document).ready(function ($) {


    var orderData = {}
    const nameInput = $('#ptc_wc_order_name');
    const phoneInput = $('#ptc_wc_order_phone');
    const shippingAddressInput = $('#ptc_wc_shipping_address');
    const totalPriceDom = $('#ptc_wc_order_total_price');
    const totalPriceInput = $('#ptc_wc_order_price');
    const totalWeightInput = $('#ptc_wc_order_weight');
    const totalQuantityInput = $('#ptc_wc_order_quantity');
    const orderItemsDom = $('#ptc_wc_order_items');
    const orderTotalItemsDom = $('#ptc_wc_total_order_items');
    const ptcModal = $('#ptc-custom-modal');

    $('.column-pathao').on('click', function (e) {
        e.preventDefault();
    });

    $('.ptc-open-modal-button').on('click', async function (e) {
        e.preventDefault();
        var orderID = $(this).data('order-id');
        $('#ptc_wc_order_number').val(orderID);  // Set the Order ID in a hidden field
        ptcModal.show();
        clearModalData();
        getOrderInfoAndPopulateModalData(orderID);
    });

    $('.close').on('click', function () {
        $('#custom-modal').hide();
        orderData = {};
    });

    // Close the modal if clicked outside the modal content
    ptcModal.on('click', function (e) {
        if ($(e.target).closest('.modal-content').length === 0) {
            ptcModal.hide();
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
            totalPriceInput.val(orderData.total);
            totalWeightInput.val(orderData.total_weight);
            totalQuantityInput.val(orderData.total_items);

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
        totalPriceInput.val('');
        totalWeightInput.val('');
        totalQuantityInput.val('');
    }

    $('#ptc-submit-button').on('click', function (event) {

        let orderId = $('#ptc_wc_order_number').val();
        const orderData = {
            merchant_order_id: orderId,
            recipient_name: $('#ptc_wc_order_name').val(),
            recipient_phone: $('#ptc_wc_order_phone').val().replace('+88'),
            recipient_address: $('#ptc_wc_shipping_address').val(),
            recipient_city: $('#city').val(),
            recipient_zone: $('#zone').val(),
            recipient_area: $('#area').val(),
            amount_to_collect: $('#ptc_wc_order_price').val(),
            store_id: $('#store').val(),
            delivery_type: 48, // Replace with actual delivery type
            item_type: 2, // Replace with actual item type
            item_quantity: totalQuantityInput.val(),
            item_weight: totalWeightInput.val(),
            amount_to_collect: +$('#ptc_wc_order_price').val(),
        };

        $.post({
            url: ajaxurl,
            headers: {
                'X-WPTC-Nonce': ptcSettings.nonce
            },
            data: {
                action: "create_order_to_ptc",
                order_data: orderData
            },
            success: function (response) {
               console.log(response);

               let consignmentId = response.data.consignment_id;

               $(`[data-order-id="${orderId}"].ptc-open-modal-button`).parent().html(`<pre> ${consignmentId} </pre>`);

               ptcModal.hide();
               
            },
            error: function (response) {
                alert(response?.responseJSON?.data?.message)
            }
        });

    });

});


jQuery(document).ready(function ($) {

    $.post(ajaxurl, {
        action: 'get_cities',
    }, function (response) {
        const cities = response.data;

        let options = '<option value="">Select city</option>';
        cities.forEach(function (city) {
            options += `<option value="${city.city_id}">${city.city_name}</option>`;
        });

        $('#city').html(options);
    });

    $.post(ajaxurl, {
        action: 'get_stores',
    }, function (response) {
        const stores = response.data;

        let options = '<option value="">Select store</option>';
        stores.forEach(function (store) {
            options += `<option value="${store.store_id}">${store.store_name}</option>`;
        });

        $('#store').html(options);
    });


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
