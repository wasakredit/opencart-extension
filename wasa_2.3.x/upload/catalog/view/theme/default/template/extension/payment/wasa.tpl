<div class="row">
  <div class="col-md-12">
    <?php if (isset($error)) { ?>
      <div class="alert alert-warning"><i class="fa fa-check-circle"></i>&nbsp;&nbsp;<?php echo $error ?></div>
    <?php } else { ?>
        <?php echo $response ?>
        <script>
          window.wasaCheckout.init();
        </script>

      <script>
          var orderReferences = [
              { key: "partner_checkout_id", value: "<?php echo $order_reference_id ?>" },
              { key: "partner_reserved_order_number", value: "<?php echo $order_reference_id ?>" }
          ];

          var redirect = '<?php echo $redirect ?>';
          var order_reference_id = '<?php echo $order_reference_id ?>';

          var options = {
              onComplete: function(orderReferences){
                  updateWasa();
              },
              onRedirect: function(orderReferences){
                  createOrder(); 
              },
              onCancel: function(orderReferences){
                  window.location.href = 'index.php?route=checkout/checkout';
              }
          };   

          window.wasaCheckout.init(options);

          setTimeout(function(){
              id_wasakredit = $('#wasaIframe').attr('src').split("id=")[1];
          }, 1000);

          function updateWasa(){

            $.ajax({
                type: "POST",
                url: '<?php echo $ajax ?>',
                data: {
                  id_wasakredit: id_wasakredit,
                  option: 'checkout'
                },
                success: function(response) {
                    if (response) {
                      if (response['processed'] == true) {
                          createOrder();  
                      }else{
                          window.location.href = 'index.php?route=checkout/checkout';
                      }
                    }
                }
            });
            return true;
          }

          function createOrder(){
            window.location.href = redirect;
          }

      </script>
    <?php } ?>


  </div>
</div>
