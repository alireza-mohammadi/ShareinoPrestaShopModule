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
                                {$shareinoCategories}
                            </div>
                            <div class="panel-footer ">
                                <button type="submit" class="btn btn-info btn-lg pull-right"
                                        name="organize_categories_submit"
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

