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
                <form class="form-horizontal" action="{$url|escape:'htmlall':'UTF-8'}" method="post">

                    <div class="row">
                        <div class="col-sm-12">
                            <h3 class="text-center" style="padding: 10px;">
                                جهت ارسال تخفیف‌ها می بایست دکمه همسان‌سازی زیر را بفشارید
                            </h3>
                        </div>
                    </div>
                    <div class="col-sm-12">
                        <div class="panel">

                            <div class="panel-footer ">
                                <button type="submit" class="btn btn-info btn-lg pull-right"
                                        name="sync_discount"
                                        style="padding: ">همسان سازی
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>



<div class="container-fluid">
    <div class="row">
        <div class="col-xs-12 text-center">
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}shareino/views/img/logo.png" alt="" title=""
                 style="margin-bottom: 20px;"/>
        </div>
    </div>
</div>

