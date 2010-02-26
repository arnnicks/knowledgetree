<form id="<?php echo $step_name; ?>" action="index.php?step_name=<?php echo $step_name; ?>" method="post">
	<p class="title">Apply Applying Templates</p>
	<p class="description">You can select a base folder layout to apply to your root folder of KnowledgeTree.</p>
	<div id="step_content_<?php echo $step_name; ?>" class="step">
		<div class="field ">
      	<p class="descriptiveText">Choose a template, if you would like to generate predefined folders.</p>
			<p class="errorMessage"></p>
				<select name="data[templateId]">
					<option selected="selected" value="">- No template -</option>
					<?php 
						foreach ($aFolderTemplates as $oFolderTemplate) {
							echo "<option onclick=\"javascript:{showFolderTemplateTree('{$oFolderTemplate->getId()}')}\" value=\"{$oFolderTemplate->getId()}\">".$oFolderTemplate->getName()."</option>";
						}
					?>
	    </select>
	</div>
	<div id="templates_tree">
		<table cellspacing="0" class="tree_table">
		<?php 
			foreach ($aFolderTemplates as $oFolderTemplate) {
				?>
				<tr style="display:none;" id="template_<?php echo $oFolderTemplate->getId(); ?>">
					<td>
						<div class="tree_icon tree_folder closed">&nbsp;</div> 
					</td>
					<td>
						<label>
							<?php echo $oFolderTemplate->getName(); ?>
						</label>
					</td>
				</tr>
				<tr style="display:none;" id="templates_<?php echo $oFolderTemplate->getId(); ?>">
				<?php
			}
		?>
		</table>
	</div>
	</div>
	<input type="submit" name="Skip" value="Skip" class="button_skip"/>
	<input type="submit" name="Next" value="Next" class="button_next"/>
</form>
<script type="text/javascript">
	$("#duname").focus();
</script>
<?php if (AJAX) { echo $html->js('form.js'); } ?>