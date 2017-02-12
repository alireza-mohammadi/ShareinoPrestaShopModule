<div class="panel col-xs-12">
    <div class="panel-heading">
        عملیات
    </div>
    <div class="panel-body">
        <div class="container-fluid">
            <div class="row">
                <div class="col-xs-12">

                    <form class="" action="{$actionUrl}" method="post" id="syncAllProductsForm" data-token='{$token}'
                          data-operation="start">

                        <button type="submit" class="btn btn-default" name="shareino_synchronize_all" id="syncSumblit">
                            <span class="glyphicon glyphicon-send" aria-hidden="true" style="display: inline;"></span>

                            همسان سازی همه
                        </button>
                        <a href="{$url|escape:'htmlall':'UTF-8'}" class="btn btn-default"><span
                                    class="glyphicon glyphicon-cog"></span>
                            تنظیمات ماژول</a>


                    </form>
                    <br/>
                    <div class="progress" hidden>
                        <div class="progress-bar progress-bar-striped active"
                             id="sync-progress"
                             role="progressbar" aria-valuenow="45" aria-valuemin="0" aria-valuemax="100">
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

</div>
{if isset($list)}
    {$list}
{/if}

<script>
    $(document).ready(function () {

        var productIDs ={$productIDs|json_encode};

        var lenght = productIDs.length;

        var stop = false;
        var submitProgress = $("#sync-progress");
        var step = 0,
            chunk = 75;

        var token = $("#syncAllProductsForm").attr('data-token');

        $("#syncAllProductsForm").on("submit", function (event) {

            // Cancel Submit
            event.preventDefault();

            var $this = $(this);
            console.log($this.attr('action'));

            operation = $this.attr("data-operation");

            var submitBtn = $("#syncSumblit");

            submitProgress.show();
            var i, j, slices;

            if (operation == "start") {

                submitBtn.html("انصراف");
                $this.attr("data-operation", "stop");

                $(".progress").show(500);
                setPercentage();


            }
            else if (operation == "stop") {

                submitBtn.html("همسان سازی همه");
                $this.attr("data-operation", "start");

                submitProgress.css("width", "0%")
                    .attr("aria-valuemin", "0%");
                $(".progress").hide(500);

                stop = true;
                lenght = productIDs.length;
            }

            SyncProducts();

            l("finish");

        });


        function SyncProducts() {
            if (!stop && productIDs.length > 0) {

                IDs = productIDs.splice(0, chunk);

                $.ajax({
                    type: 'POST',
                    dataType: 'json',
                    url: 'ajax-tab.php',
                    data: {
                        //required parameters
                        ajax: true,
                        controller: 'AdminSynchronize',
                        action: 'SyncProducts',
                        token: token,

                        ids: IDs
                    },
                })
                    .done(function () {
                        step++;
                        l(step);
                        setPercentage();
                        SyncProducts();

                    })
                    .fail(function () {

                    });
            }
        }

        function setPercentage() {

            var percentage = Math.round(((step * chunk) * 100) / lenght);


            percentage = percentage > 100 ? 100 : percentage;

            submitProgress
                .css("width", percentage + "%")
                .attr("aria-valuemin", percentage + "%")
                .html(percentage + "%");
        }

        function l(log) {
            console.log(log);
        }
    });
</script>
