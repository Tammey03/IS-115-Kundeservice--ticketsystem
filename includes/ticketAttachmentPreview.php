<?php $attachmentCount = count($attachments); ?>
<div class="sak-attachment-gallery" data-attachment-gallery role="group" aria-label="Vedleggsgalleri">
    <div class="sak-attachment-stage">
        <?php foreach ($attachments as $index => $attachment): ?>
            <?php
            $attachmentUrl = '../vedlegg.php?attachment_id=' . (int) $attachment['attachment_id'];
            $attachmentName = htmlspecialchars($attachment['filename'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            ?>
            <div data-attachment-slide<?= $index === 0 ? '' : ' hidden' ?>>
                <?php if ($attachment['is_image']): ?>
                    <img class="sak-attachment-preview" src="<?= $attachmentUrl ?>" alt="<?= $attachmentName ?>" loading="lazy">
                <?php else: ?>
                    <iframe class="sak-attachment-preview" src="<?= $attachmentUrl ?>#toolbar=0" title="<?= $attachmentName ?>" loading="lazy"></iframe>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <?php if ($attachmentCount > 1): ?>
            <button class="sak-attachment-arrow sak-attachment-prev" type="button" data-attachment-step="-1" aria-label="Forrige vedlegg">‹</button>
            <button class="sak-attachment-arrow sak-attachment-next" type="button" data-attachment-step="1" aria-label="Neste vedlegg">›</button>
        <?php endif; ?>
    </div>

    <?php if ($attachmentCount > 1): ?>
        <div class="sak-attachment-thumbnails">
            <?php foreach ($attachments as $index => $attachment): ?>
                <button type="button" data-attachment-index="<?= $index ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>"
                    aria-label="Vis vedlegg <?= $index + 1 ?>, <?= htmlspecialchars($attachment['filename'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>">
                    <?php if ($attachment['is_image']): ?>
                        <img src="../vedlegg.php?attachment_id=<?= (int) $attachment['attachment_id'] ?>" alt="" loading="lazy">
                    <?php else: ?>
                        <span>PDF</span>
                    <?php endif; ?>
                </button>
            <?php endforeach; ?>
        </div>
        <p class="sak-attachment-count" role="status"><span data-attachment-position>1</span> av <?= $attachmentCount ?></p>
    <?php endif; ?>
</div>
