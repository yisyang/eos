				</div>
				<div id="jq-dialog-modal"></div>
			</div>
			<div id="footer">Footer goes here</a>
				<span style="width:100px;margin-left:50px;"><a class="jqDialog" href="/eos/report-bugs.php">Report a Bug</a></span>
				<span style="width:100px;margin-left:50px;"><a href="/eos/promote-site.php">Welfare Center</a></span>
				<span style="width:100px;margin-left:50px;"><a href="/tos.php">ToS</a></span>
				<span style="float:right;padding-right: 5px;"><a class="info">Server Time: <?= date("F j, Y, g:i A") ?>
				<?php if($show_page_gen_time) echo '<span>Generated in '.(microtime(1) - $timestart).' s</span>'; ?>
				</a></span>
			</div>
		</div>
	</body>
</html>