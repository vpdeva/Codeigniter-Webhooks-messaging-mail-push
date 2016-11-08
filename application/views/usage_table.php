<style>
    .hiddenRow {
        padding: 0 !important;
    }
</style>
<div id="container" style="padding: 10px;">
    <div class="well"> 
        <table class="table" style="table-layout: fixed;"> 
            <caption>Totals</caption>
            <thead> 
                <tr> 
                    <th class="" width="20%"></th> 
                    <th class="text-right" width="20%">Email</th> 
                    <th class="text-right" width="20%">SMS</th> 
                    <th class="text-right" width="20%">APN</th>
                    <th class="text-right" width="20%">Total</th>
                </tr> 
            </thead> 
            <tbody>
                <tr>
                    <td class="" width="20%"><strong>Total</strong></td>
                    <td class="text-right" width="20%"><?=$total['email_sent']?></td>
                    <td class="text-right" width="20%"><?=$total['sms_sent']?></td>
                    <td class="text-right" width="20%"><?=$total['pn_sent']?></td>
                    <td class="text-right" width="20%"><?=$total['total']?></td>
                </tr>
            </tbody> 
        </table> 
    </div>
    
    <div class="well"> 
        <table class="table table-striped" style="table-layout: fixed;"> 
            <caption>Department Totals</caption>
            <thead> 
                <tr> 
                    <th class="" width="20%">Dept</th> 
                    <th class="text-right" width="20%">Email</th> 
                    <th class="text-right" width="20%">SMS</th> 
                    <th class="text-right" width="20%">APN</th>
                    <th class="text-right" width="20%">Total</th>
                </tr> 
            </thead> 
            <tbody>
                
                <? foreach($department as $dept=>$info):?>
                <tr data-toggle="collapse" data-target=".<?=str_replace(' ', '', $dept).'-col'?>" style="cursor: pointer;">
                    <td class="" width="20%"><?=$dept?></td>
                    <td class="text-right" width="20%"><?=$info['email_sent']?></td>
                    <td class="text-right" width="20%"><?=$info['sms_sent']?></td>
                    <td class="text-right" width="20%"><?=$info['pn_sent']?></td>
                    <td class="text-right" width="20%"><?=$info['total']?></td>
                </tr>
                <tr>
                    <td class="hiddenRow" colspan="5">
                        <div class="collapse <?=str_replace(' ', '', $dept).'-col'?>">
                            <table class="table" style="margin-bottom:0px">
                                <thead>
                                    <tr>
                                        <th colspan="5" width="30%" style="padding-left: 20px;">User</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <? foreach($info['users'] as $username=>$info_user): ?>
                                    <tr>
                                        <td class="" style="padding-left: 20px;" width="20%"><?=$username?></td>
                                        <td class="text-right"  width="20%"><?=$info_user['email_sent']?></td>
                                        <td class="text-right" width="20%"><?=$info_user['sms_sent']?></td>
                                        <td class="text-right" width="20%"><?=$info_user['pn_sent']?></td>
                                        <td class="text-right" width="20%"><?=$info_user['total']?></td>
                                    </tr>
                                    <? endforeach;?>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>

                <? endforeach;?>
                
            </tbody> 
        </table> 
    </div>
    
    <div class="well"> 
        <table class="table table-striped"> 
            <caption>User Totals</caption>
            <thead> 
                <tr> 
                    <th class="" width="20%">User</th> 
                    <th class="text-right" width="20%">Email</th> 
                    <th class="text-right" width="20%">SMS</th> 
                    <th class="text-right" width="20%">APN</th>
                    <th class="text-right" width="20%">Total</th>
                </tr> 
            </thead> 
            <tbody>
                
                <? foreach($user as $name=>$info):?>
                <tr>
                    <td class="" width="20%"><?=$name?></td>
                    <td class="text-right" width="20%"><?=$info['email_sent']?></td>
                    <td class="text-right" width="20%"><?=$info['sms_sent']?></td>
                    <td class="text-right" width="20%"><?=$info['pn_sent']?></td>
                    <td class="text-right" width="20%"><?=$info['total']?></td>
                </tr>
                <? endforeach;?>
                
            </tbody> 
        </table> 
    </div>
    
</div>