<?php echo $form->renderFormTag(url_for('@form_submit')) ?>
  <table>
    <?php echo $form->render() ?>
  </table>

  <input type="submit" value="Submit" />
</form>