<div class="alert alert-dismissible" id="syncMessageBox" role="alert" hidden>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
    </button>
    <p id="syncMessageText"></p>
</div>
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

                        <a href="{$url|escape:'htmlall':'UTF-8'}" class="btn btn-default"><span
                                    class="glyphicon glyphicon-cog"></span>
                            تنظیمات ماژول</a>
                        <button type="button" class="btn btn-default" name="shareino_send_categories" id="sendCatsBtn">
                            <span class="glyphicon glyphicon-send" aria-hidden="true" style="display: inline;"></span>
                            ارسال تمامی دسته بندی ها
                        </button>
                        <button type="submit" class="btn btn-default" name="shareino_synchronize_all" id="syncSumblit">
                            <span class="glyphicon glyphicon-send" aria-hidden="true" style="display: inline;"></span>
                            همسان سازی همه
                        </button>
                    </form>


                    <div id="loadingBox" hidden class="text-center"><img
                                src="{$module_dir|escape:'htmlall':'UTF-8'}shareino/views/img/loader.gif"
                                alt="" title=""/>
                    </div>

                    <div class="text-center" id="progress" hidden>
                        <p class="label label-default" id="progressText"></p>
                        <div class="progress">
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
        var progressText = $("#progressText");
        var chunk = 50;

        var token = $("#syncAllProductsForm").attr('data-token');
        var messageBox = $("#syncMessageBox");
        var messageText = $("#syncMessageText");
        var syncFrom = $("#syncAllProductsForm");
        var submitBtn = $("#syncSumblit");

        $("#sendCatsBtn").on('click', function () {

            $("#loadingBox").show();

            $.ajax({
                type: 'POST',
                dataType: 'json',
                url: 'ajax-tab.php',
                data: {
                    //required parameters
                    ajax: true,
                    controller: 'AdminSynchronize',
                    action: 'SendCats',
                    token: token,
                },
            }).done(function (data) {
                l(data);
                if (data.status) {
                    $("#loadingBox").hide();
                    messageBox.addClass("alert-success");
                    messageText.html("تمامی دسته بندی ها با موفقیت ارسال شدند");
                    messageBox.show(500);
                }
                else {
                    $("#loadingBox").hide();
                    messageText.html(data.data);
                    messageBox.show(500);
                    messageBox.addClass("alert-danger");
                    stop();
                }
            }).fail(function () {

            });

        });

        syncFrom.on("submit", function (event) {

            // Cancel Submit
            event.preventDefault();

            productIDs = {$productIDs|json_encode};
            lenght = productIDs.length;

            operation = syncFrom.attr("data-operation");


            submitProgress.show();

            if (operation == "start") {

                submitBtn.html("انصراف");
                syncFrom.attr("data-operation", "stop");
                stop = false;
                $("#progress").show(500);
                setPercentage();


            }
            else if (operation == "stop") {
                stopSync();
            }

            SyncProducts();


        });


        function SyncProducts() {

            console.log(productIDs.length);

            if (productIDs.length <= 0) {
                messageText.html("تمامی محصولات با سایت شیرینو همسان سازی شدند");
                messageBox.show(500);
                messageBox.addClass('alert-success');
                submitBtn.html("همسان سازی همه");
                return;
            }

            if (!stop) {
                var IDs = productIDs.splice(0, chunk);

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
                }).done(function (data) {
                    if (data.status) {
                        setPercentage();
                        SyncProducts();
                    }
                    else {
                        messageText.html(data.data);
                        messageBox.show(500);
                        messageBox.addClass("alert-danger");
                        stop();
                    }
                }).fail(function () {

                });

            }
        }

        function setPercentage() {

            var percentage = Math.round(((lenght - productIDs.length) * 100) / lenght);


            percentage = percentage > 100 ? 100 : percentage;

            var text = " تعداد " + (lenght - productIDs.length) + " از " + lenght + " محصول همسان سازی شد. ";

            progressText.html(text)
            submitProgress
                .css("width", percentage + "%")
                .attr("aria-valuemin", percentage + "%")
                .html(percentage + "%");
        }

        function l(log) {
            console.log(log);
        }

        function stopSync() {

            submitBtn.html("همسان سازی همه");
            syncFrom.attr("data-operation", "start");

            submitProgress.css("width", "0%")
            submitProgress.css("width", "0%")
                .attr("aria-valuemin", "0%");
            $("#progress").hide(500);
            stop = true;
            lenght = productIDs.length;
        }
    });
</script>
