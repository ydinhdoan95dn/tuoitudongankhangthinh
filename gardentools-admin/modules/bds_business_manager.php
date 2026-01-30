<?php
if (!defined('TTH_SYSTEM')) { die('Please stop!'); }
//
?>

<!-- Menu path -->
<div class="row">
    <ol class="breadcrumb">
        <li>
	        <a href="<?=ADMIN_DIR?>"><i class="fa fa-home"></i> Trang chủ</a>
        </li>
        <li>
            <i class="fa fa-edit"></i> Quản lý nội dung
        </li>
        <li>
            <i class="fa fa-money"></i> BĐS Kinh doanh
        </li>
		<span style="float: right; font-weight: bold;">
			<a href="?<?=TTH_PATH?>=bds_business_add&id=1"><i class="fa fa-plus-square-o"></i> Thêm tin mới</a>
		</span>
    </ol>
</div>
<!-- /.row -->
<?=dashboardCoreAdmin(); ?>
<?php
$del =  isset($_GET['del']) ? $_GET['del']+0 : 0;
if($del != 0) {
	$dir_dest = ROOT_DIR . DS .'uploads';

	// Lấy id menu cha.
	$parent = 0;
	$db->table = "bds_business_menu";
	$db->condition = "bds_business_menu_id = ".$del;
	$db->order = "";
	$rows = $db->select();
	foreach($rows as $row) {
		$parent = $row['parent']+0;
	}

	// Cập nhật menu con.
	$db->table = "bds_business_menu";
	$data = array(
		'parent'=>$parent,
		'modified_time'=>time(),
		'user_id'=>$_SESSION["user_id"]
	);
	$db->condition = "parent = ".$del;
	$db->update($data);

	// Xóa ảnh bds_business liên quan.
	$db->table = "bds_business";
	$db->condition = "bds_business_menu_id  = ".$del;
	$db->order = "";
	$rows = $db->select();
	foreach($rows as $row) {
		$list_img = "";
		$db->table = "uploads_tmp";
		$db->condition = "upload_id = ".($row['upload_id']+0);
		$db->order = "";
		$rows = $db->select();
		foreach ($rows as $row){
			$list_img = $row['list_img'];
		}
		$img = explode(";",$list_img);
		if(count($img)>0) {
			for($j=0;$j<count($img);$j++) {
				if($img[$j] != ""){
					$mask = $dir_dest . DS . "photos" . DS . '*'.$img[$j];
					if (glob($mask))
						array_map("unlink", glob($mask));
				}
			}
		}
		$db->table = "uploads_tmp";
		$db->condition = "upload_id = ".($row['upload_id']+0);
		$db->delete();
	}

	// Xóa csdl bds_business liên quan.
	$db->table = "bds_business";
	$db->condition = "bds_business_menu_id  = ".$del;
	$db->delete();

	// Xóa ảnh menu.
	$db->table = "bds_business_menu";
	$db->condition = "bds_business_menu_id  = ".$del;
	$rows = $db->select();
	foreach($rows as $row) {
		$mask = $dir_dest . DS . "bds_business_menu" . DS . '*'.$row['img'];
		if(!empty($row['img']) && glob($mask)) {
			array_map("unlink", glob($mask));
		}
	}

	// Xóa csld menu.
	$db->table = "bds_business_menu";
	$db->condition = "bds_business_menu_id  = ".$del;
	$db->delete();

	loadPageSucces("Đã xóa Mục thành công.","?".TTH_PATH."=bds_business_manager");

}
?>
<div class="row">
    <div class="col-lg-12">
	    <div class="panel panel-default panel-no-border">
		    <div class="table-responsive">
                <table class="table table-manager table-hover">
                    <thead>
                    <tr>
                        <th colspan="2">Mục</th>
                        <th>Sắp xếp</th>
                        <th>Trạng thái</th>
                        <th>Nổi bật</th>
                        <th>Hình ảnh</th>
                        <th>Chức năng</th>
                        <th>Nội dung</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $db->table = "category";
                    $db->condition = "type_id = 18";
                    $db->order = "sort ASC";
                    $rows = $db->select();
                    $i = 0;
                    $countList = 0;
                    $countList = $db->RowCount;
                    foreach($rows as $row) {
                    ?>
                    <tr class="category">
                        <td><?=stripslashes($row['name'])?></td>
                        <td>&nbsp;</td>
	                    <?php
	                    //-----------Kiểm tra phân quyền ---------------------------------------
	                    if(in_array("category_edit;".$row["category_id"],$corePrivilegeSlug)) {
	                    ?>
	                    <td align="center">
		                    <?=showSort("sortcat".$row["category_id"]."", $countList,$row["sort"], "90%", 1, $row["category_id"], 'category', 1);?>
	                    </td>
	                    <td align="center">
		                    <?=($row["is_active"]+0==0)?
			                    '<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="editStatus('.$row["category_id"].',1,\'editstatus\',\'category\')"></div>'
			                    :
			                    '<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="editStatus('.$row["category_id"].',0,\'editstatus\',\'category\')"></div>'
		                    ?>
	                    </td>
	                    <td align="center">
		                    <?=($row["hot"]+0==0)?
			                    '<div class="btn-event-close" data-toggle="tooltip" data-placement="top"title="Mở" onclick="editStatus('.$row["category_id"].',1,\'edithot\',\'category\')"></div>'
			                    :
			                    '<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="editStatus('.$row["category_id"].',0,\'edithot\',\'category\')"></div>'
		                    ?>
	                    </td>
	                    <?php
	                    } else {
	                    ?>
	                    <td align="center">
		                    <?=showSort("sortcat".$row["category_id"]."", $countList,$row["sort"], "90%", 1, $row["category_id"], 'category', 0);?>
	                    </td>
	                    <td align="center">
		                    <?=($row["is_active"]+0==0)?
			                    '<div class="btn-event-close alertManager" data-toggle="tooltip" data-placement="top" title="Mở"></div>'
			                    :
			                    '<div class="btn-event-open alertManager" data-toggle="tooltip" data-placement="top" title="Đóng"></div>'
		                    ?>
	                    </td>
	                    <td align="center">
		                    <?=($row["hot"]+0==0)?
			                    '<div class="btn-event-close alertManager" data-toggle="tooltip" data-placement="top"title="Mở"></div>'
			                    :
			                    '<div class="btn-event-open alertManager" data-toggle="tooltip" data-placement="top" title="Đóng"></div>'
		                    ?>
	                    </td>
	                    <?php }
	                    //----------- end if ------------
	                    ?>
                        <td align="center">
	                        <?=($row["img"]=='no')?
		                        '<img data-toggle="tooltip" data-placement="top" title="Không có hình" src="images/error.png">'
		                        :
		                        '<img id="popover-'.$row["category_id"].'" class="btn-popover" title="'.stripslashes($row["name"]).'" src="images/OK.png">
		                        <script>
		                            var image = \'<img src="../uploads/category/'.$row["img"].'">\';
		                            $(\'#popover-'.$row["category_id"].'\').popover({placement: \'bottom\', content: image, html: true});
								</script>'
	                        ?>
                        </td>
                        <td align="center">
                            <a href="?<?=TTH_PATH?>=bds_business_menu_add&id_cat=<?=$row["category_id"]?>"><img data-toggle="tooltip" data-placement="left" title="Thêm mục" src="images/add.png"></a>
                            &nbsp;
                            <a href="?<?=TTH_PATH?>=category_edit&id_cat=<?=$row["category_id"]?>"><img data-toggle="tooltip" data-placement="top" title="Chỉnh sửa" src="images/edit.png"></a>
	                        &nbsp;
	                        <span style="width: 16px; height: 1px; display: inline-block;""></span>
                        </td>
                        <td align="center">&nbsp;</td>
                        <?php
                        loadMenuCategory($db,0,0,$row["category_id"]+0);
                        ?>
                    </tr>
                    <?php
                    }
                    ?>
                    </tbody>
                </table>
            </div>
            <!-- /.table-responsive -->
        </div>
        <!-- /.panel -->
    </div>
    <!-- /.col-lg-6 -->
</div>

<?php
/* ***** MODULE MỚI CHỈ HOÀN THIỆN CHO MENU 3 CẤP **** */
/**
 * @param $db
 * @param $level
 * @param $parent
 * @param $category_id
 */
function loadMenuCategory($db, $level, $parent, $category_id){
	global $corePrivilegeSlug;

	$db->table = "bds_business_menu";
	$db->condition = "category_id = ".$category_id." and parent = ".$parent;
	$db->order = "sort ASC";
	$rows2 = $db->select();
	$i = 0;
	$countList = 0;
	$countList = $db->RowCount;
	foreach($rows2 as $row) {
	    ?>
	    <tr>
	        <td>&nbsp;</td>
	        <td style="padding: 0 0 0 <?=$level?>px;"><img src="images/node.png" /> <?=stripslashes($row['name'])?></td>
			<?php
			//-----------Kiểm tra phân quyền ---------------------------------------
			if($parent==0) $width = '80%';
			else $width = '70%';
			if(in_array("bds_business_menu_edit;".$row["category_id"],$corePrivilegeSlug)) {
			?>
			<td align="center">
				<?=showSort("sortpro".$row["bds_business_menu_id"]."", $countList,$row["sort"], $width, 0, $row["bds_business_menu_id"] ,'bds_business_menu', 1);?>
			</td>
			<td align="center">
			    <?=($row["is_active"]+0==0)?
				    '<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="editStatus('.$row["bds_business_menu_id"].',1,\'editstatus\',\'bds_business_menu\')"></div>'
				    :
				    '<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="editStatus('.$row["bds_business_menu_id"].',0,\'editstatus\',\'bds_business_menu\')"></div>'
			    ?>
		    </td>
		    <td align="center">
			    <?=($row["hot"]+0==0)?
				    '<div class="btn-event-close" data-toggle="tooltip" data-placement="top" title="Mở" onclick="editStatus('.$row["bds_business_menu_id"].',1,\'edithot\',\'bds_business_menu\')"></div>'
				    :
				    '<div class="btn-event-open" data-toggle="tooltip" data-placement="top" title="Đóng" onclick="editStatus('.$row["bds_business_menu_id"].',0,\'edithot\',\'bds_business_menu\')"></div>'
			    ?>
		    </td>
			<?php
			}
			else {
			?>
			<td align="center">
				<?=showSort("sortpro".$row["bds_business_menu_id"]."", $countList,$row["sort"], $width, 0, $row["bds_business_menu_id"] ,'bds_business_menu', 0);?>
			</td>
			<td align="center">
				<?=($row["is_active"]+0==0)?
					'<div class="btn-event-close alertManager" data-toggle="tooltip" data-placement="top" title="Mở"></div>'
					:
					'<div class="btn-event-open alertManager" data-toggle="tooltip" data-placement="top" title="Đóng"></div>'
				?>
			</td>
			<td align="center">
				<?=($row["hot"]+0==0)?
					'<div class="btn-event-close alertManager" data-toggle="tooltip" data-placement="top" title="Mở"></div>'
					:
					'<div class="btn-event-open alertManager" data-toggle="tooltip" data-placement="top" title="Đóng"></div>'
				?>
			</td>
			<?php }
			//----------- end if ------------
			?>
	        <td align="center">
		        <?=($row["img"]=='no')?
			        '<img data-toggle="tooltip" data-placement="top" title="Không có hình" src="images/error.png">'
			        :
			        '<img id="popover-'.$row["bds_business_menu_id"].'" class="btn-popover" title="'.stripslashes($row["name"]).'" src="images/OK.png">
			        <script>
							var image = \'<img src="../uploads/bds_business_menu/'.$row["img"].'">\';
							$(\'#popover-'.$row["bds_business_menu_id"].'\').popover({placement: \'bottom\', content: image, html: true});
					</script>'
		        ?>
	        </td>
            <td align="center">
	            <?php
				if ($level < 30){
	            ?>
                <a href="?<?=TTH_PATH?>=bds_business_menu_add&id_cat=<?=$row["category_id"]?>&id_pro=<?=$row["bds_business_menu_id"]?>"><img data-toggle="tooltip" data-placement="left" title="Thêm mục" src="images/add.png"></a>
                &nbsp;
				<?php } else { ?>
					<span style="width: 16px; height: 1px; display: inline-block;""></span>
					&nbsp;
				<?php } ?>
	            <a href="?<?=TTH_PATH?>=bds_business_menu_edit&id=<?=$row["bds_business_menu_id"]?>"><img data-toggle="tooltip" data-placement="top" title="Chỉnh sửa" src="images/edit.png"></a>
                &nbsp;
	            <?php
	            if(!in_array("bds_business_menu_del;".$row["category_id"],$corePrivilegeSlug)) {
		            ?>
		            <a class="alertManager" style="cursor: pointer;"><img data-toggle="tooltip" data-placement="right" title="Xóa mục" src="images/remove.png"></a>
	            <?php
	            }
	            else {
		            ?>
		            <a class="confirmManager" style="cursor: pointer;" id="?<?=TTH_PATH?>=bds_business_manager&del=<?=$row["bds_business_menu_id"]?>"><img data-toggle="tooltip" data-placement="right" title="Xóa mục" src="images/remove.png"></a>
	            <?php }
	            //----------- end if ------------
	            ?>
            </td>
	        <td align="center">
		        <a href="?<?=TTH_PATH?>=bds_business_list&id=<?=$row['bds_business_menu_id']?>"><img data-toggle="tooltip" data-placement="top" title="Danh sách bài viết" src="images/list.png"></a>
	        </td>
	        <?php
			if ($level < 30){
	            loadMenuCategory($db, $level+30, $row["bds_business_menu_id"]+0, $row["category_id"]+0);
			}
	        ?>
	    </tr>
	<?php
	}
}
?>
<script>
	$(".confirmManager").click(function() {
		var element = $(this);
		var action = element.attr("id");
		confirm("Tất cả các Dữ liệu, Hình ảnh liên quan sẽ được xóa và không thể phục hồi.\nMục con của mục này sẽ được đẩy lên một bậc.\nBạn có muốn thực hiện không?", function() {
			if(this.data == true) window.location.href = action;
		});
	});
	$(".alertManager").boxes('alert', 'Bạn không được phân quyền với chức năng này.');
</script>
