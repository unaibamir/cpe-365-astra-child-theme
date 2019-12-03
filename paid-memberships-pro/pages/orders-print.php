<?php
/**
 * Template for Print Invoices
 *
 * @since 1.8.6
 */
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice # <?php echo $order->code; ?></title>
    <link href="https://fonts.googleapis.com/css?family=Open+Sans&display=swap" rel="stylesheet">
    <style>
    	body {
    		font-family: 'Open Sans', sans-serif;
    	}
        .main, .header {
            display: block;
        }
        .right {
            display: inline-block;
            float: right;
        }
        .alignright {
            text-align: right;
        }
        .aligncenter {
            text-align: center;
        }
        .invoice, .invoice tr, .invoice th, .invoice td {
            border: 1px solid;
            border-collapse: collapse;
            padding: 4px;
        }
        .invoice {
            width: 100%;
            margin-bottom: 50px;
        }
        /* table.invoice thead {
        		    border-bottom: 1px solid;
        		} */
        .sub-heading {
        	text-decoration: underline;
        	font-weight: bold;
        }
        @media screen {
            body {
                max-width: 50%;
                margin: 0 auto;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div>
            <h2 class="aligncenter"><?php bloginfo('sitename') ; ?></h2>
        </div>
        <div class="right">
            <table>
                <tr>
                    <td><?php echo __('Invoice #: ', 'paid-memberships-pro') . '&nbsp;' . $order->code; ?></td>
                </tr>
                <tr>
                    <td>
                        <?php echo __('Date:', 'paid-memberships-pro') . '&nbsp;' . date_i18n(get_option('date_format'), $order->timestamp); ?>
                    </td>
                </tr>
            </table>
        </div>
    </header>
    <main class="main">
    	<?php echo wpautop( get_option("cpe_billing_info") ); ?>

		<p><span class="sub-heading"><?php _e( "Billing Information:", CPE_LANG ); ?></span></p>

        <p>
        	<?php _e("Student Name", CPE_LANG); ?>: <?php echo $order->billing->name; ?><br>
        	
            <?php echo pmpro_formatAddress(
                '',
                $order->billing->street,
                '',
                $order->billing->city,
                $order->billing->state,
                $order->billing->zip,
                $order->billing->country,
                $order->billing->phone
            ); ?>
        </p>

        <p><span class="sub-heading"><?php _e( "Payment Information:", CPE_LANG ); ?></span></p>

		<p>
			<span><?php _e("Payment Method:"); ?></span> <?php echo !empty( $order->payment_type ) ? $order->payment_type : "TESTING"; ?> <br>
			<span><?php _e("Transaction ID:"); ?></span> <?php echo !empty( $order->subscription_transaction_id ) ? $order->subscription_transaction_id : $order->payment_transaction_id; ?>
		</p>

        <p></p>
        <table class="invoice">
        	<thead>
	            <tr>
	                <th><?php _e('Item', 'paid-memberships-pro'); ?></th>
	                <th style="width: 150px;"><?php _e('Price', 'paid-memberships-pro'); ?></th>
	                <!-- <th><?php _e('Amount', 'paid-memberships-pro'); ?></th> -->
	            </tr>
        	</thead>
        	<tbody>
	            <tr>
	                <td><?php echo $level->name; ?></td>
	                <td class="alignright"><?php echo pmpro_formatPrice($order->subtotal); ?></td>
	            </tr>
	            <tr>
	                <th colspan="1" class="alignright"><?php _e('Subtotal', 'paid-memberships-pro'); ?></th>
	                <td class="alignright"><?php echo pmpro_formatPrice($order->subtotal); ?></td>
	            </tr>
	            <tr>
	                <th colspan="1" class="alignright"><?php _e('Tax', 'paid-memberships-pro'); ?></th>
	                <td class="alignright"><?php echo pmpro_formatPrice($order->tax); ?></td>
	            </tr>
	            <tr>
	                <th colspan="1" class="alignright"><?php _e('Total', 'paid-memberships-pro'); ?></th>
	                <th class="alignright"><?php echo pmpro_formatPrice($order->total); ?></th>
	            </tr>
        	</tbody>
        </table>
    </main>
</body>
</html>
