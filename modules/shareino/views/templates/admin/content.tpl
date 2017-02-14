{*
 * 2015-2016 Shareino
 *
 * NOTICE OF LICENSE
 *
 * This source file is for module that make sync Product With shareino server
 * https://github.com/SaeedDarvish/PrestaShopShareinoModule
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Shareino to newer
 * versions in the future. If you wish to customize Shareino for your
 * needs please refer to https://github.com/SaeedDarvish/PrestaShopShareinoModule for more information.
 *
 * @author    Saeed Darvish <sd.saeed.darvish@gmail.com>
* @copyright 2015-2016 Shareino Co
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  Tejarat Ejtemaie Eram
*}
<div class="alert alert-dismissible" id="syncMessageBox" role="alert" hidden>
    <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span>
    </button>
    <p id="syncMessageText"></p>
</div>
<div class="container-fluid">
    <div class="row">
        <div class="panel panel-body col-sm-12">
            <div class="row">
                <div class="col-sm-12">
                    <h2 class="text-center" style="padding: 10px;">
                        برای معادل سازی دسته بندی ها یکی از دسته های فروشگاه خود و در مقابل دسته بندی معادل در سیستم
                        شیراینو را انتخاب کنید
                    </h2>
                </div>
            </div>
            <div class="row">
                <form class="form-horizontal" action="{$url|escape:'htmlall':'UTF-8'}" method="post">
                    <div class="col-sm-6">
                        {$storeCategoryBox}
                    </div>

                    <div class="col-sm-6">
                        <div class="panel">
                            <div class="tree-panel-heading-controls clearfix">
                                <i class="icon-tag"></i>&nbsp;دسته بندی های شیرینو
                                <div class="tree-actions pull-right">
                                </div>
                            </div>
                            <div class="panel-body" style="min-height: 250px; max-height: 250px; overflow-y: scroll;">
                                <div id="loadingBox" hidden class="text-center"><img
                                            src="{$module_dir|escape:'htmlall':'UTF-8'}shareino/views/img/loader.gif"
                                            alt="" title=""/>
                                </div>
                                <div id="shareinoCategorisBox"></div>
                            </div>
                            <div class="panel-footer ">
                                <button type="submit" class="btn btn-info btn-lg pull-right"
                                        name="organize_categories_submit"
                                        id="getCats"
                                        style="padding: ">درج
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>

{if isset($list)}
    {$list}
{/if}

<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12 text-center">
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}shareino/views/img/logo.png" alt="" title=""
                 style="margin-bottom: 20px;"/>
        </div>
    </div>
</div>

<script>

    $(document).ready(function () {
        var messageBox = $("#syncMessageBox");
        var messageText = $("#syncMessageText");

        $("#loadingBox").show();
        var appToken ={$token|json_encode};

        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: 'ajax-tab.php',
            data: {
                //required parameters
                ajax: true,
                controller: 'AdminManageCats',
                action: 'GetShareinoCategories',
                token: appToken,
            },
        }).done(function (data) {
            $("#loadingBox").hide();

            if (data.status) {
                $("#shareinoCategorisBox").html(treeCategories(data.data.categories));
            }
            else {
                messageText.html(data.data);
                messageBox.show(500);
                messageBox.addClass("alert-danger");
                stop();
            }

        }).fail(function () {
            $("#loadingBox").hide();
            messageText.html("خطا لطفا مجددا تلاش کنیدس");
            messageBox.show(500);
            messageBox.addClass("alert-danger");
        });

    });

    function treeCategories(pcategories, level = 0) {

        var out = "";
        if (level === 0) {
            out = '<ul id="associated-categories-tree" class="cattree tree">';
        } else {
            out = '<ul class="tree" style="">';
        }

        var i = 0;


        var categories = $.map(pcategories, function (el) {
            return el
        });

        for (i = 0; i < categories.length; i++) {

            var category = categories[i];


            if (category.children.length > 0) {
                out += '<li class="tree-folder"><span class="tree-folder-name">';
                out += sprintf('<input type="checkbox" name="shareinoCategories[]" value="%s">', category.id);
                out += '<i class="icon-folder-close" style="padding: 5px;"></i>';
                out += sprintf('<label class="tree-toggler" style="padding: 5px;">  %s  </label>', category.name);
                out += '</span>';
                out += treeCategories(category.children, level + 1);
            } else {
                out += '<li class="tree-item"><span class="tree-item-name">';
                out += sprintf('<input type="checkbox" name="shareinoCategories[]" value="%s">', category.id);
                out += '<i class="tree-dot"></i>';
                out += sprintf('<label class="tree-toggler"> %s </label>', category.name);
                out += '</span>';
            }
            out += '</li>';
        }

        out += '</ul>';

        return out;
    }
</script>

