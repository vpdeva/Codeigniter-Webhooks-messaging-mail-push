<div id="container">
    <div> 
        <table class="table table-striped"> 
            <thead> 
                <tr> 
                    <th>#</th> 
                    <th>batch</th> 
                    <th>To</th> 
                    <th>Subject</th> 
                    <th>Sent</th> 
                    <!-- <th>Delivered</th> -->
                    <th>Opened</th> 
                    <th>Bounced</th> 
                    <th>View</th> 
                </tr> 
            </thead> 
            <tbody>
            <?$i=1;?>
            <? foreach($messages as $message):?>
                <tr class="<?=(!$message['sent'] ? 'danger' : ($message['delivered'] ? 'success' : ($message['bounced'] ? 'warning' : 'info'))) ?>">
                    <td><?=$message['uuid']?></td>
                    <td><?=$message['batch']?></td>
                    <td><?=htmlspecialchars($message['to'])?></td>
                    <td><?=$message['subject']?></td>
                    <td><?=($message['sent'] ? "Yes" : "No")?></td>
                    <td><?=(array_key_exists('opened', $message) ? ($message['opened'] ? "Yes" : "No") : "-")?></td>
                    <td><?=(array_key_exists('bounced', $message) ? ($message['bounced'] ? "Yes" : "No") : "-")?></td>
                    <td><button type="button btn-sm" class="btn btn-info" data-toggle="modal" data-target="#view-email-<?=$i?>">View</button></td>
                </tr>
                
                <!-- Modal -->
                <div class="modal fade" id="view-email-<?=$i?>" tabindex="-1" role="dialog" aria-labelledby="view-email-label-<?=$i?>">
                    <div class="modal-dialog" role="document">
                        <div class="modal-content" style="width:700px; margin-left: -60px;">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="view-email-label-<?=$i?>"><?=$message['subject']?></h4>
                            </div>
                            <div class="modal-body">
                                <iframe src="https://api.school.kiwi/MESSAGING/1.0/view/email/<?=$message['lookup']?>" style="width:100%; height:800px;" frameborder="0"></iframe>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /Modal -->
                
            <?$i++;?>
            <? endforeach;?>
            </tbody> 
        </table> 
    </div>
</div>