<?php include('db_connect.php'); ?>

<div class="container-fluid">
	<style>
		input[type=checkbox] {
			/* Double-sized Checkboxes */
			-ms-transform: scale(1.5); /* IE */
			-moz-transform: scale(1.5); /* FF */
			-webkit-transform: scale(1.5); /* Safari and Chrome */
			-o-transform: scale(1.5); /* Opera */
			transform: scale(1.5);
			padding: 10px;
		}
		.list-group-item + .list-group-item {
			border-top-width: 1px !important;
		}
		td {
			vertical-align: middle !important;
		}
		td p {
			margin: unset;
		}
		img {
			max-width: 100px;
			max-height: 150px;
		}
	</style>
	<div class="col-lg-12">
		<div class="row mb-4 mt-4">
			<div class="col-md-12">
				<!-- Additional header or message if needed -->
			</div>
		</div>
		<div class="row">
			<!-- Table Panel -->
			<div class="col-md-12">
				<div class="card">
					<div class="card-header">
						<b>Topic List</b>
						<span class="">
							<button class="btn btn-primary btn-block btn-sm col-sm-2 float-right" type="button" id="new_topic">
								<i class="fa fa-plus"></i> Create Topic
							</button>
						</span>
					</div>
					<div class="card-body">
						<ul class="w-100 list-group" id="topic-list">
							<?php
							// Get categories
							$tag = $conn->query("SELECT * FROM categories ORDER BY name ASC");
							while ($row = $tag->fetch_assoc()):
								$tags[$row['id']] = $row['name'];
							endwhile;

							// Get topics
							$topic = $conn->query("SELECT t.*, u.name FROM topics t INNER JOIN users u ON u.id = t.user_id ORDER BY unix_timestamp(date_created) DESC");
							while ($row = $topic->fetch_assoc()):
								// Decode the content so that HTML tags are rendered correctly
								$desc = html_entity_decode($row['content']);
								$view = $conn->query("SELECT * FROM forum_views WHERE topic_id=" . $row['id'])->num_rows;
								$comments = $conn->query("SELECT * FROM comments WHERE topic_id=" . $row['id'])->num_rows;
								$replies = $conn->query("SELECT * FROM replies WHERE comment_id IN (SELECT id FROM comments WHERE topic_id=" . $row['id'] . ")")->num_rows;
							?>
								<li class="list-group-item mb-4">
									<div>
										<?php if ($_SESSION['user_id'] == $row['user_id']): ?>
											<div class="dropleft float-right mr-4">
												<a class="text-dark" href="javascript:void(0)" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
													<span class="fa fa-ellipsis-v"></span>
												</a>
												<div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
													<a class="dropdown-item edit_topic" data-id="<?php echo $row['id'] ?>" href="javascript:void(0)">Edit</a>
													<a class="dropdown-item delete_topic" data-id="<?php echo $row['id'] ?>" href="javascript:void(0)">Delete</a>
												</div>
											</div>
										<?php endif; ?>
										<span class="float-right mr-4">
											<small><i>Created: <?php echo date('M d, Y h:i A', strtotime($row['date_created'])) ?></i></small>
										</span>
										<a href="index.php?page=view_forum&id=<?php echo $row['id'] ?>" class="filter-text">
											<?php echo $row['title'] ?>
										</a>
									</div>
									<hr>
									<!-- Display the decoded content as HTML -->
									<div class="truncate filter-text">
										<?php echo $desc; ?>
									</div>
									<p class="row justify-content-left">
										<span class="badge badge-success text-white">
											<i>Posted By: <?php echo $row['name'] ?></i>
										</span>
									</p>
									<hr>
									<span class="float-left badge badge-secondary text-white">
										<?php echo number_format($view) ?> view/s
									</span>
									<span class="float-left badge badge-primary text-white ml-2">
										<i class="fa fa-comments"></i> <?php echo number_format($view) ?> comment/s <?php echo $replies > 0 ? " and " . number_format($replies) . ' replies' : '' ?>
									</span>
									<span class="float-right">
										<span>Tags: </span>
										<?php
										foreach (explode(",", $row['category_ids']) as $cat):
										?>
											<span class="badge badge-info text-white ml-2">
												<?php echo $tags[$cat] ?>
											</span>
										<?php endforeach; ?>
									</span>
								</li>
							<?php endwhile; ?>
						</ul>
					</div>
				</div>
			</div>
			<!-- End Table Panel -->
		</div>
	</div>
</div>

<script>
	$(document).ready(function() {
		$('table').dataTable();
	});
	$('#topic-list').JPaging({
		pageSize: 15,
		visiblePageSize: 10
	});

	$('#new_topic').click(function() {
		uni_modal("New Entry", "manage_topic.php", 'mid-large');
	});

	$('.edit_topic').click(function() {
		uni_modal("Edit Topic", "manage_topic.php?id=" + $(this).attr('data-id'), 'mid-large');
	});

	$('.delete_topic').click(function() {
		_conf("Are you sure to delete this topic?", "delete_topic", [$(this).attr('data-id')], 'mid-large');
	});

	function delete_topic($id) {
		start_load();
		$.ajax({
			url: 'ajax.php?action=delete_topic',
			method: 'POST',
			data: { id: $id },
			success: function(resp) {
				if (resp == 1) {
					alert_toast("Data successfully deleted", 'success');
					setTimeout(function() {
						location.reload();
					}, 1500);
				}
			}
		});
	}
</script>
