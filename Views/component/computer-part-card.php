<div class="card" style="width: 18rem;">
    <div class="card-body">
        <h5 class="card-title"><?= htmlspecialchars($part->getName()) ?></h5>
        <h6 class="card-subtitle mb-2 text-muted">
            <?= htmlspecialchars($part->getType()) ?> - <?= htmlspecialchars($part->getBrand()) ?>
        </h6>
        <p class="card-text">
            <strong>Id:</strong> <?= htmlspecialchars($part->getId() ?? 'N/A') ?><br />
            <strong>Model:</strong> <?= htmlspecialchars($part->getModelNumber() ?? 'N/A') ?><br />
            <strong>Release Date:</strong> <?= htmlspecialchars($part->getReleaseDate() ?? 'N/A') ?><br />
            <strong>Description:</strong> <?= htmlspecialchars($part->getDescription() ?? 'N/A') ?><br />
            <strong>Performance Score:</strong> <?= htmlspecialchars($part->getPerformanceScore() ?? 'N/A') ?><br />
            <strong>Market Price:</strong> $<?= htmlspecialchars($part->getMarketPrice() ?? 'N/A') ?><br />
            <strong>RSM:</strong> <?= htmlspecialchars($part->getRsm() ?? 'N/A') ?><br />
            <strong>Power Consumption:</strong> <?= htmlspecialchars($part->getPowerConsumptionW() ?? 'N/A') ?> W<br />
            <strong>Dimensions:</strong> 
                <?= htmlspecialchars($part->getLengthM() ?? 'N/A') ?> m × 
                <?= htmlspecialchars($part->getWidthM() ?? 'N/A') ?> m × 
                <?= htmlspecialchars($part->getHeightM() ?? 'N/A') ?> m<br />
            <strong>Lifespan:</strong> <?= htmlspecialchars($part->getLifespan() ?? 'N/A') ?> years<br />
        </p>
        <p class="card-text">
            <small class="text-muted">
                Last updated on <?= htmlspecialchars($part->getTimeStamp()?->getUpdatedAt() ?? 'N/A') ?>
            </small>
        </p>
    </div>
</div>

