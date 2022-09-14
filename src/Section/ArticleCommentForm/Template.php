<?php

namespace Be\App\Cms\Section\ArticleCommentForm;

use Be\Be;
use Be\Theme\Section;

class Template extends Section
{

    public array $positions = ['middle', 'center'];

    public array $routes = ['Cms.Article.detail'];


    private function css()
    {
        echo '<style type="text/css">';
        echo $this->getCssBackgroundColor('article-comment');
        echo $this->getCssPadding('article-comment');
        echo $this->getCssMargin('article-comment');
        echo '</style>';
    }

    public function display()
    {
        if ($this->config->enable === 0) {
            return;
        }

        $this->css();

        echo '<div class="article-comment">';

        if ($this->position === 'middle' && $this->config->width === 'default') {
            echo '<div class="be-container">';
        }

        echo $this->page->tag0('be-section-title');
        echo $this->config->title;
        echo $this->page->tag1('be-section-title');

        echo $this->page->tag0('be-section-content');
        echo '<form id="form-article-comment">';
        echo '<input type="hidden" name="article_id" value="' . $this->page->article->id . '" maxlength="60">';

        $my = Be::getUser();

        echo '<div class="be-floating be-mt-150">';
        echo '<input type="text" name="name" class="be-input" placeholder="' . beLang('App.Cms', 'ARTICLE.COMMENT.NAME_PLACEHOLDER') . '" value="' . ($my->isGuest() ? '' : $my->name) . '" maxlength="60">';
        echo '<label class="be-floating-label">' . beLang('App.Cms', 'ARTICLE.COMMENT.NAME_LABEL');
        if ($this->config->nameRequired) {
            echo '<span class="be-c-red">*</span>';
        }
        echo '</label>';
        echo '</div>';

        echo '<div class="be-floating be-mt-150">';
        echo '<input type="text" name="email" class="be-input" placeholder="' . beLang('App.Cms', 'ARTICLE.COMMENT.EMAIL_PLACEHOLDER') . '" value="' . ($my->isGuest() ? '' : $my->email) . '" maxlength="60">';
        echo '<label class="be-floating-label">' . beLang('App.Cms', 'ARTICLE.COMMENT.EMAIL_LABEL');
        if ($this->config->emailRequired) {
            echo '<span class="be-c-red">*</span>';
        }
        echo '</label>';
        echo '</div>';

        echo '<div class="be-floating be-mt-150">';
        echo '<textarea name="content" id="article-comment-content" class="be-textarea" placeholder="' . beLang('App.Cms', 'ARTICLE.COMMENT.CONTENT_PLACEHOLDER') . '" rows="6"></textarea>';
        echo '<label class="be-floating-label">' . beLang('App.Cms', 'ARTICLE.COMMENT.CONTENT_LABEL') . '</label>';
        echo '</div>';

        echo '<div class="be-mt-150">';
        echo '<input type="submit" class="be-btn be-btn-main" value="' . beLang('App.Cms', 'ARTICLE.COMMENT.SUBMIT') .'">';
        echo '<input type="reset" class="be-btn be-ml-100" value="' . beLang('App.Cms', 'ARTICLE.COMMENT.RESET') .'">';
        echo '</div>';

        echo '</form>';

        echo $this->page->tag1('be-section-content');

        if ($this->position === 'middle' && $this->config->width === 'default') {
            echo '</div>';
        }

        echo '</div>';

        $this->js();
    }

    private function js()
    {
        ?>
        <script type="text/javascript" language="javascript" src="<?PHP echo \Be\Be::getProperty('App.Cms')->getWwwUrl(); ?>/js/jquery.validate.min.js"></script>
        <script>
            $(function () {
                $("#form-article-comment").validate({
                    rules: {
                        <?php if ($this->config->nameRequired) { ?>
                        name: {
                            required: true
                        },
                        <?php } ?>

                        <?php if ($this->config->emailRequired) { ?>
                        email: {
                            required: true,
                            email: true
                        },
                        <?php } ?>

                        content: {
                            required: true,
                            maxlength: 500
                        }
                    },
                    messages: {
                        <?php if ($this->config->nameRequired) { ?>
                        name: {
                            required: "<?php echo beLang('App.Cms', 'ARTICLE.COMMENT.NAME_REQUIRED'); ?>"
                        },
                        <?php } ?>

                        <?php if ($this->config->emailRequired) { ?>
                        email: {
                            required: "<?php echo beLang('App.Cms', 'ARTICLE.COMMENT.EMAIL_REQUIRED'); ?>",
                            email: "<?php echo beLang('App.Cms', 'ARTICLE.COMMENT.EMAIL_FORMAL_ERROR'); ?>"
                        },
                        <?php } ?>

                        content: {
                            required: "<?php echo beLang('App.Cms', 'ARTICLE.COMMENT.CONTENT_REQUIRED'); ?>",
                            maxlength: "<?php echo beLang('App.Cms', 'ARTICLE.COMMENT.CONTENT_MAX_WORDS'); ?>"
                        }
                    },
                    submitHandler: function (form) {
                        let $submit = $(".be-btn-main", $(form));
                        let sValue = $submit.val();
                        $submit.prop("disabled", true).val("<?php echo beLang('App.Cms', 'ARTICLE.COMMENT.SUBMITTING'); ?>");
                        $.ajax({
                            type: "POST",
                            url: "<?php echo beUrl('Cms.ArticleComment.create'); ?>",
                            data: $(form).serialize(),
                            dataType: "json",
                            success: function (json) {
                                $submit.prop("disabled", false).val(sValue);
                                alert(json.message);
                                if (json.success) {
                                    $("#article-comment-content").val("");
                                }
                            }
                        });
                    }
                })
            });
        </script>
        <?php
    }
}

