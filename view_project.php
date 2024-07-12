<?php
include 'db_connect.php';
$stat = array("Pending","Started","On-Progress","On-Hold","Over Due","Done");
$qry = $conn->query("SELECT * FROM project_list where id = ".$_GET['id'])->fetch_array();
foreach($qry as $k => $v){
	$$k = $v;
}
$tprog = $conn->query("SELECT * FROM task_list where project_id = {$id}")->num_rows;
$cprog = $conn->query("SELECT * FROM task_list where project_id = {$id} and status = 3")->num_rows;
$prog = $tprog > 0 ? ($cprog/$tprog) * 100 : 0;
$prog = $prog > 0 ?  number_format($prog,2) : $prog;
$prod = $conn->query("SELECT * FROM user_productivity where project_id = {$id}")->num_rows;
if($status == 0 && strtotime(date('Y-m-d')) >= strtotime($start_date)):
if($prod  > 0  || $cprog > 0)
  $status = 2;
else
  $status = 1;
elseif($status == 0 && strtotime(date('Y-m-d')) > strtotime($end_date)):
$status = 4;
endif;
$manager = $conn->query("SELECT *,concat(firstname,' ',lastname) as name FROM users where id = $manager_id");
$manager = $manager->num_rows > 0 ? $manager->fetch_array() : array();
?>
<div class="col-lg-12">
	<div class="row">
		<div class="col-md-12">
			<div class="callout callout-info">
				<div class="col-md-12">
					<div class="row">
						<div class="col-sm-6">
							<dl>
								<dt><b class="border-bottom border-primary">Project Name</b></dt>
								<dd><?php echo ucwords($name) ?></dd>
								<dt><b class="border-bottom border-primary">Description</b></dt>
								<dd><?php echo html_entity_decode($description) ?></dd>
							</dl>
						</div>
						<div class="col-md-6">
							<dl>
								<dt><b class="border-bottom border-primary">Start Date</b></dt>
								<dd><?php echo date("F d, Y",strtotime($start_date)) ?></dd>
							</dl>
							<dl>
								<dt><b class="border-bottom border-primary">End Date</b></dt>
								<dd><?php echo date("F d, Y",strtotime($end_date)) ?></dd>
							</dl>
							<dl>
								<dt><b class="border-bottom border-primary">Status</b></dt>
								<dd>
									<?php
									  if($stat[$status] =='Pending'){
									  	echo "<span class='badge badge-secondary'>{$stat[$status]}</span>";
									  }elseif($stat[$status] =='Started'){
									  	echo "<span class='badge badge-primary'>{$stat[$status]}</span>";
									  }elseif($stat[$status] =='On-Progress'){
									  	echo "<span class='badge badge-info'>{$stat[$status]}</span>";
									  }elseif($stat[$status] =='On-Hold'){
									  	echo "<span class='badge badge-warning'>{$stat[$status]}</span>";
									  }elseif($stat[$status] =='Over Due'){
									  	echo "<span class='badge badge-danger'>{$stat[$status]}</span>";
									  }elseif($stat[$status] =='Done'){
									  	echo "<span class='badge badge-success'>{$stat[$status]}</span>";
									  }
									?>
								</dd>
							</dl>
							<dl>
								<dt><b class="border-bottom border-primary">Project Manager</b></dt>
								<dd>
									<?php if(isset($manager['id'])) : ?>
									<div class="d-flex align-items-center mt-1">
										<img class="img-circle img-thumbnail p-0 shadow-sm border-info img-sm mr-3" src="assets/uploads/<?php echo $manager['avatar'] ?>" alt="Avatar">
										<b><?php echo ucwords($manager['name']) ?></b>
									</div>
									<?php else: ?>
										<small><i>Manager Deleted from Database</i></small>
									<?php endif; ?>
								</dd>
							</dl>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-4">
			<div class="card card-outline card-primary">
				<div class="card-header">
					<span><b>Team Member/s:</b></span>
					<div class="card-tools">
						<!-- <button class="btn btn-primary bg-gradient-primary btn-sm" type="button" id="manage_team">Manage</button> -->
					</div>
				</div>
				<div class="card-body">
					<ul class="users-list clearfix">
						<?php 
						if(!empty($user_ids)):
							$members = $conn->query("SELECT *,concat(firstname,' ',lastname) as name FROM users where id in ($user_ids) order by concat(firstname,' ',lastname) asc");
							while($row=$members->fetch_assoc()):
						?>
								<li>
			                        <img src="assets/uploads/<?php echo $row['avatar'] ?>" alt="User Image">
			                        <a class="users-list-name" href="javascript:void(0)"><?php echo ucwords($row['name']) ?></a>
			                        <!-- <span class="users-list-date">Today</span> -->
		                    	</li>
						<?php 
							endwhile;
						endif;
						?>
					</ul>
				</div>
			</div>
		</div>
		<div class="col-md-8">
			<div class="card card-outline card-primary">
				<div class="card-header">
					<span><b>Task List:</b></span>
					<?php if($_SESSION['login_type'] != 3): ?>
					<div class="card-tools">
						<button class="btn btn-primary bg-gradient-primary btn-sm" type="button" id="new_task"><i class="fa fa-plus"></i> New Task</button>
					</div>
				<?php endif; ?>
				</div>
				<div class="card-body p-0">
					<div class="table-responsive">
					<table class="table table-condensed m-0 table-hover">
						<colgroup>
							<col width="5%">
							<col width="25%">
							<col width="30%">
							<col width="15%">
							<col width="15%">
						</colgroup>
						<thead>
							<th>#</th>
							<th>Task</th>
							<th>Description</th>
							<th>Status</th>
							<th>Action</th>
						</thead>
						<tbody>
							<?php 
							$i = 1;
							$tasks = $conn->query("SELECT * FROM task_list where project_id = {$id} order by id asc");
							while($row=$tasks->fetch_assoc()):
								$trans = get_html_translation_table(HTML_ENTITIES,ENT_QUOTES);
								unset($trans["\""], $trans["<"], $trans[">"], $trans["<h2"]);
								$desc = strtr(html_entity_decode($row['description']),$trans);
								$desc=str_replace(array("<li>","</li>"), array("",", "), $desc);
							?>
								<tr>
			                        <td class="text-center"><?php echo $i++ ?></td>
			                        <td class=""><b><?php echo ucwords($row['task']) ?></b></td>
			                        <td class=""><p class="truncate"><?php echo strip_tags($desc) ?></p></td>
			                        <td>
			                        	<?php 
			                        	if($row['status'] == 1){
									  		echo "<span class='badge badge-secondary'>Pending</span>";
			                        	}elseif($row['status'] == 2){
									  		echo "<span class='badge badge-primary'>On-Progress</span>";
			                        	}elseif($row['status'] == 3){
									  		echo "<span class='badge badge-success'>Done</span>";
			                        	}
			                        	?>
			                        </td>
			                        <td class="text-center">
										<button type="button" class="btn btn-default btn-sm btn-flat border-info wave-effect text-info dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
					                      Action
					                    </button>
					                    <div class="dropdown-menu" style="">
					                      <a class="dropdown-item view_task" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"  data-task="<?php echo $row['task'] ?>">View</a>
					                      <div class="dropdown-divider"></div>
					                      <?php if($_SESSION['login_type'] != 3): ?>
					                      <a class="dropdown-item edit_task" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"  data-task="<?php echo $row['task'] ?>">Edit</a>
					                      <div class="dropdown-divider"></div>
					                      <a class="dropdown-item delete_task" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">Delete</a>
					                  <?php endif; ?>
					                    </div>
									</td>
		                    	</tr>
							<?php 
							endwhile;
							?>
						</tbody>
					</table>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="row">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<b>Members Progress/Activity</b>
					<div class="card-tools">
						<button class="btn btn-primary bg-gradient-primary btn-sm" type="button" id="new_productivity"><i class="fa fa-plus"></i> New Productivity</button>
					</div>
				</div>
				<div class="card-body">
					<?php 
					$progress = $conn->query("SELECT p.*,concat(u.firstname,' ',u.lastname) as uname,u.avatar,t.task 
											FROM user_productivity p 
											inner join users u on u.id = p.user_id 
											inner join task_list t on t.id = p.task_id 
											where p.project_id = $id 
											order by unix_timestamp(p.date_created) desc ");
					while($row = $progress->fetch_assoc()):
					?>
						<div class="post">

		                    <div class="user-block">
		                      	<?php if($_SESSION['login_id'] == $row['user_id']): ?>
		                      	<span class="btn-group dropleft float-right">
								  <span class="btndropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="cursor: pointer;">
								    <i class="fa fa-ellipsis-v"></i>
								  </span>
								  <div class="dropdown-menu">
								  	<a class="dropdown-item manage_progress" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>"  data-task="<?php echo $row['task'] ?>">Edit</a>
			                      	<div class="dropdown-divider"></div>
				                     <a class="dropdown-item delete_progress" href="javascript:void(0)" data-id="<?php echo $row['id'] ?>">Delete</a>
								  </div>
								</span>
								<?php endif; ?>
		                        <img class="img-circle img-bordered-sm" src="assets/uploads/<?php echo $row['avatar'] ?>" alt="user image">
		                        <span class="username">
		                          <a href="#"><?php echo ucwords($row['uname']) ?>[ <?php echo ucwords($row['task']) ?> ]</a>
		                        </span>
								
		                        <span class="description">
		                        	<span class="fa fa-calendar-day"></span>
		                        	<span><b><?php echo date('M d, Y',strtotime($row['date'])) ?></b></span>
		                        	<span class="fa fa-user-clock"></span>
                      				<span>Start: <b><?php echo date('h:i A',strtotime($row['date'].' '.$row['start_time'])) ?></b></span>
		                        	<span> | </span>
                      				<span>End: <b><?php echo date('h:i A',strtotime($row['date'].' '.$row['end_time'])) ?></b></span>
	                        	</span>

	                        	

		                    </div>
							<!-- /.user-block -->
							<div>
								<span><?php echo html_entity_decode(ucfirst($row['comment'])) ?></span>
							</div>

							<?php if(isset($row['file']) && $row['file'] != null): 
									$file = $row['file']; ?>

								<div class="d-flex justify-content-justify">
									<img class="img-bordered-sm" id="myImg" style="float: left; height: auto; max-height: 150px; max-width: 120px;" src="assets/uploads/<?php echo $file; ?>" alt="user upload">
								</div>

								<!-- The Modal -->
								<div id="myModal" class="modal">
									<span class="close">&times;</span>
									<img class="modal-content" id="img01">
									<div id="caption"></div>
								</div>
							<?php endif; ?>

						
							<form action="" class="productivity-reply">
								<div class="comment">
									<?php
										$comments = $conn->query("SELECT r.*,concat(u.firstname,' ',u.lastname) as uname,u.avatar,r.reply as reply 
																FROM user_productivity_replies r 
																inner join users u on u.id = r.user_id 
																inner join user_productivity p on p.id = r.productivity_id 
																where r.project_id = $id and r.productivity_id = ".$row['id']."  
																order by unix_timestamp(r.date) desc");
										while($comment = $comments->fetch_assoc()):
									?>
									<small>Comments:</small><br>
									<small>
										<img class="img-circle" style="width: 20px; height: 20px;" src="assets/uploads/<?php echo $comment['avatar'] ?>" alt="user image">
										<span class="username">
											<a href="#"><?php echo ucwords($comment['uname']) ?></a>
										</span>
										<p>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<?php echo $comment['reply']; ?></p>
									</small>
									<?php endwhile; ?>
									<span class="reply-link" style="cursor: pointer; color: #007bff;" onclick="showReplyField(this)">
										<i class="fa fa-reply" aria-hidden="true"></i> Reply
									</span>
									<div class="reply-field" style="display: none;">
										<input type="hidden" name="user_id" value="<?php echo $_SESSION['login_id']; ?>">
										<input type="hidden" name="productivity_id" value="<?php echo $row['id']; ?>">
										<input type="hidden" name="project_id" value="<?php echo $id; ?>">
										<input type="hidden" name="task_id" value="<?php echo $row['task_id']; ?>">
										<textarea class="form-control mt-2" rows="2" name="reply" placeholder="Enter your reply"></textarea>
										<button type="button" class="btn btn-primary btn-sm mt-2" onclick="submitReply(this)">Submit</button>
									</div>
								</div>
							</form>
								
	
							<!-- <div class="comment">
							<small>
								<strong><a href="#" onclick="showReplyField(this)"><i class="fa fa-reply" aria-hidden="true"></i> Reply</a></strong>

								<div class="reply-field" style="display: none;">
									
                          	</small>
							</div> -->
							
	                    </div>
	                    <div class="post clearfix"></div>
                    <?php endwhile; ?>
				</div>
			</div>
		</div>
	</div>
</div>
<style>

	#myImg {
		border-radius: 5px;
		cursor: pointer;
		transition: 0.3s;
	}

	#myImg:hover {opacity: 0.7;}

	/* The Modal (background) */
	.modal {
		display: none; /* Hidden by default */
		position: fixed; /* Stay in place */
		/* z-index: 9999; */
		padding-top: 100px; /* Location of the box */
		left: 0;
		top: 0;
		width: 100%; /* Full width */
		height: 100%; /* Full height */
		overflow: auto; /* Enable scroll if needed */
		background-color: rgb(0,0,0); /* Fallback color */
		background-color: rgba(0,0,0,0.9); /* Black w/ opacity */
	}

	/* Modal Content (image) */
	.modal-content {
		margin: auto;
		display: block;
		width: 80%;
		max-width: 800px;
	}

	/* Caption of Modal Image */
	#caption {
		margin: auto;
		display: block;
		width: 80%;
		max-width: 700px;
		text-align: center;
		color: #ccc;
		padding: 10px 0;
		height: 150px;
	}

	/* Add Animation */
	.modal-content, #caption {  
		-webkit-animation-name: zoom;
		-webkit-animation-duration: 0.6s;
		animation-name: zoom;
		animation-duration: 0.6s;
	}

	@-webkit-keyframes zoom {
		from {-webkit-transform:scale(0)} 
		to {-webkit-transform:scale(1)}
	}

	@keyframes zoom {
		from {transform:scale(0)} 
		to {transform:scale(1)}
	}

	/* The Close Button */
	.close {
		position: absolute;
		top: 15px;
		right: 35px;
		color: #f1f1f1;
		font-size: 40px;
		font-weight: bold;
		transition: 0.3s;
	}

	.close:hover,
	.close:focus {
		color: #bbb;
		text-decoration: none;
		cursor: pointer;
	}

	/* 100% Image Width on Smaller Screens */
	@media only screen and (max-width: 700px){
		.modal-content {
			width: 100%;
		}
	}


	.users-list>li img {
	    border-radius: 50%;
	    height: 67px;
	    width: 67px;
	    object-fit: cover;
	}
	.users-list>li {
		width: 33.33% !important
	}
	.truncate {
		-webkit-line-clamp:1 !important;
	}
</style>
<script>

document.addEventListener('DOMContentLoaded', function() {
            var modal = document.getElementById("myModal");

            // Get the image and insert it inside the modal - use its "alt" text as a caption
            var img = document.getElementById("myImg");
            var modalImg = document.getElementById("img01");
            var captionText = document.getElementById("caption");
            img.onclick = function() {
                modal.style.display = "block";
                modalImg.src = this.src;
                captionText.innerHTML = this.alt;
            }

            // Get the <span> element that closes the modal
            var span = document.getElementsByClassName("close")[0];

            // When the user clicks on <span> (x), close the modal
            span.onclick = function() {
                modal.style.display = "none";
            }
        });
		
	// var modal = document.getElementById("myModal");

	// // Get the image and insert it inside the modal - use its "alt" text as a caption
	// var img = document.getElementById("myImg");
	// var modalImg = document.getElementById("img01");
	// var captionText = document.getElementById("caption");
	// img.onclick = function(){
	// 	modal.style.display = "block";
	// 	modalImg.src = this.src;
	// 	captionText.innerHTML = this.alt;
	// }

	// // Get the <span> element that closes the modal
	// var span = document.getElementsByClassName("close")[0];

	// // When the user clicks on <span> (x), close the modal
	// span.onclick = function() { 
	// 	modal.style.display = "none";
	// }

	$('#new_task').click(function(){
		uni_modal("New Task For <?php echo ucwords($name) ?>","manage_task.php?pid=<?php echo $id ?>","mid-large")
	})
	$('.edit_task').click(function(){
		uni_modal("Edit Task: "+$(this).attr('data-task'),"manage_task.php?pid=<?php echo $id ?>&id="+$(this).attr('data-id'),"mid-large")
	})
	$('.view_task').click(function(){
		uni_modal("Task Details","view_task.php?id="+$(this).attr('data-id'),"mid-large")
	})
	$('#new_productivity').click(function(){
		uni_modal("<i class='fa fa-plus'></i> New Progress","manage_progress.php?pid=<?php echo $id ?>",'large')
	})
	$('.manage_progress').click(function(){
		uni_modal("<i class='fa fa-edit'></i> Edit Progress","manage_progress.php?pid=<?php echo $id ?>&id="+$(this).attr('data-id'),'large')
	})
	$('.delete_progress').click(function(){
	_conf("Are you sure to delete this progress?","delete_progress",[$(this).attr('data-id')])
	})
	function delete_progress($id){
		start_load()
		$.ajax({
			url:'ajax.php?action=delete_progress',
			method:'POST',
			data:{id:$id},
			success:function(resp){
				if(resp==1){
					alert_toast("Data successfully deleted",'success')
					setTimeout(function(){
						location.reload()
					},1500)

				}
			}
		})
	}

	function showReplyField(link) {
		// Find the parent comment element
		var comment = link.closest('.comment');
		
		// Find the reply field within the parent comment element
		var replyField = comment.querySelector('.reply-field');
		
		// Toggle the display of the reply field
		if (replyField.style.display === 'none' || replyField.style.display === '') {
			replyField.style.display = 'block';
		} else {
			replyField.style.display = 'none';
		}
	}

	// $('#productivity-reply').submit(function(e) {
	// 	e.preventDefault();  // Prevent the default form submission
	// 	start_load();  // Assuming this function shows a loading indicator

	// 	// Log the form data for debugging
	// 	var productivity_id = find('input[name="productivity_id"]').val();
	// 	var id = form.find('input[name="id"]').val();
	// 	var task_id = form.find('input[name="task_id"]').val();
	// 	var reply = form.find('textarea[name="reply"]').val();

	// 	var formData = new FormData();
	// 	formData.append('productivity_id', productivity_id);
	// 	formData.append('id', id);
	// 	formData.append('task_id', task_id);
	// 	formData.append('reply', reply);

	// 	// Log the extracted data for debugging
	// 	console.log('productivity_id: ' + productivity_id);
	// 	console.log('id: ' + id);
	// 	console.log('task_id: ' + task_id);
	// 	console.log('reply: ' + reply);


	// 	$.ajax({
	// 		url: 'ajax.php?action=submit_reply',
	// 		data: formData,
	// 		cache: false,
	// 		contentType: false,
	// 		processData: false,
	// 		method: 'POST',
	// 		type: 'POST',
	// 		success: function(resp){
	// 			console.log('Server Response:', resp);  // Log the server response
	// 			if (resp == 1) {
	// 				alert_toast('Replied', "success");
	// 				// setTimeout(function(){
	// 				// 	location.reload();
	// 				// }, 1500);
	// 			} else {
	// 				console.log('Unexpected response:', resp);  // Log unexpected responses
	// 			}
	// 		},
	// 		error: function(jqXHR, textStatus, errorThrown) {
	// 			console.error('AJAX Error:', textStatus, errorThrown);  // Log any errors
	// 		}
	// 	});
	// });


function submitReply(button) {
    var form = $(button).closest('.productivity-reply');

    // Extract specific values directly
    var user_id = form.find('input[name="user_id"]').val();
    var productivity_id = form.find('input[name="productivity_id"]').val();
    var project_id = form.find('input[name="project_id"]').val();
    var task_id = form.find('input[name="task_id"]').val();
    var reply = form.find('textarea[name="reply"]').val();

    var formData = new FormData();
    formData.append('user_id', user_id);
    formData.append('productivity_id', productivity_id);
    formData.append('project_id', project_id);
    formData.append('task_id', task_id);
    formData.append('reply', reply);

    // Log the extracted data for debugging
    console.log('user_id: ' + user_id);
    console.log('productivity_id: ' + productivity_id);
    console.log('project_id: ' + project_id);
    console.log('task_id: ' + task_id);
    console.log('reply: ' + reply);

    // Perform the AJAX request
    $.ajax({
        url: 'ajax.php?action=submit_reply',
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        method: 'POST',
        type: 'POST',
        success: function(resp){
            console.log('Server Response:', resp);  // Log the server response
            if (resp == 1) {
                alert_toast('Replied', "success");
                // Uncomment if you want to reload the page after a delay
                // setTimeout(function(){
                //     location.reload();
                // }, 1500);
            } else {
                console.log('Unexpected response:', resp);  // Log unexpected responses
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('AJAX Error:', textStatus, errorThrown);  // Log any errors
        }
    });
}

	// $('#productivity-reply').submit(function(e){
	// 	e.preventDefault();  // Prevent the default form submission
	// 	start_load();  // Assuming this function shows a loading indicator

	// 	// Log the form data for debugging
	// 	var formData = new FormData($(this)[0]);
	// 	for (var pair of formData.entries()) {
	// 		console.log(pair[0] + ': ' + pair[1]);
	// 	}

	// 	$.ajax({
	// 		url: 'ajax.php?action=submit_reply',
	// 		data: formData,
	// 		cache: false,
	// 		contentType: false,
	// 		processData: false,
	// 		method: 'POST',
	// 		type: 'POST',
	// 		success: function(resp){
	// 			console.log('Server Response:', resp);  // Log the server response
	// 			if (resp == 1) {
	// 				alert_toast('Replied', "success");
	// 				// setTimeout(function(){
	// 				// 	location.reload();
	// 				// }, 1500);
	// 			} else {
	// 				console.log('Unexpected response:', resp);  // Log unexpected responses
	// 			}
	// 		},
	// 		error: function(jqXHR, textStatus, errorThrown) {
	// 			console.error('AJAX Error:', textStatus, errorThrown);  // Log any errors
	// 		}
	// 	});
	// });
</script>