jQuery(document).ready(function($) {

    $( '#add_more_extra_services_child' ).on('click', function( e ){
        e.preventDefault();

        var numVal = $(this).data("increment") + 1;
        $(this).data('increment', numVal);
        $(this).attr({
            "data-increment" : numVal
        });

        var newOption = '' +
            '<div class="more_extra_services_wrap" style="margin-top:10px;">'+
                '<div class="row">'+
                    '<div class="col-sm-6 col-xs-12">'+
                        '<div class="form-group">'+
                            '<label for="name">Enter Add on</label>'+
                            '<input type="text" name="extra_price['+numVal+'][name]" class="form-control" placeholder="Enter Add On">'+
                        '</div>'+
                    '</div>'+
                    '<div class="col-sm-6 col-xs-12">'+
                        '<div class="form-group">'+
                            '<label for="price"> Price Per Day </label>'+
                            '<input type="text" name="extra_price['+numVal+'][price]" class="form-control" placeholder="Enter Price Per Day">'+
                        '</div>'+
                    '</div>'+
                    // '<div class="col-sm-4 col-xs-12">'+
                    // '<div class="form-group">'+
                    // '<label for="type"> '+Homey_Listing.ex_type+' </label>'+

                    // '<select name="extra_price['+numVal+'][type]" class="type-select-picker selectpicker" data-live-search="false" data-live-search-style="begins">'+
                    // '<option value="single_fee">'+Homey_Listing.ex_single_fee+'</option>'+
                    // '<option value="per_night"> '+Homey_Listing.ex_per_night+'</option>'+
                    // '<option value="per_guest">'+Homey_Listing.ex_per_guest+'</option>'+
                    // '<option value="per_night_per_guest">'+Homey_Listing.ex_per_night_per_guest+'</option>'+
                    // '</select>'+
                    // '</div>'+
                    // '</div>'+
                '</div>'+
                '<div class="row">'+
                    '<div class="col-sm-12 col-xs-12">'+
                        '<button type="button" data-remove="'+numVal+'" class="remove-extra-services-child btn btn-primary btn-slim">Delete</button>'+
                    '</div>'+
                '</div>'+
            '</div>';


        $( '#more_extra_services_main').append( newOption );
        $('.type-select-picker').selectpicker('refresh');
        removeExtraServices();
    });

    var removeExtraServices = function (){

        $( '.remove-extra-services-child').on('click', function( event ){
            event.preventDefault();
            var $this = $( this );
            $this.closest( '.more_extra_services_wrap' ).remove();
        });
    }
    removeExtraServices();

    // Additional Rules
    $('.btn-single-rule').on('click',function(e){
        e.preventDefault();
        var rulesText = $('#listing_rules_add_rule').val();

        if(rulesText != ''){
            $.ajax({
                type: 'post',
                url: homey_child_ajax.ajax_url,
                dataType: 'json',
                data: {
                    'action' : 'generate_listing_rules',
                    'rulesText' : rulesText,
                },
                beforeSend: function() {
                    $('.btn-single-rule').text('Processing...');
                },
                success: function(data) {
                    if(data.success){
                        alert(data.message);
                        $(".listing-rules-row").append(data.rule_html);
                    }

                    $('.remove-btn-single-rule').on('click',function(e){
                        e.preventDefault();
                        $(this).closest('.single-rules-row').remove();
                    });
                },
                error: function(errorThrown) {},
                complete: function(){
                    $('.btn-single-rule').text('+ Add More');
                    $('#listing_rules_add_rule').val('');
                }
            });
        }else{
            alert('Please enter a rule');
        }
    });
    
}); 