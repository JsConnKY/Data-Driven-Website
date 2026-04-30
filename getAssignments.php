<?php
session_start();
include 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    echo '<div class="empty-note">You must be logged in.</div>';
    exit();
}

$currentSql = "SELECT 
                    inventoryitemlocation.ItemLocationID,
                    inventoryitem.ItemName,
                    inventoryitem.SkuCode,
                    inventoryitem.IsActive,
                    location.LocationName,
                    inventoryitemlocation.QuantityAtLocation
               FROM inventoryitemlocation
               INNER JOIN inventoryitem
                    ON inventoryitemlocation.ItemID = inventoryitem.ItemID
               INNER JOIN location
                    ON inventoryitemlocation.LocationID = location.LocationID
               ORDER BY location.LocationName, inventoryitem.ItemName";

$currentResult = $conn->query($currentSql);

if (!$currentResult) {
    echo '<div class="empty-note">Could not load assignments.</div>';
    exit();
}

$groupedAssignments = [];

while ($assignment = $currentResult->fetch_assoc()) {
    $locationName = $assignment['LocationName'];

    if (!isset($groupedAssignments[$locationName])) {
        $groupedAssignments[$locationName] = [
            'items' => [],
            'totalQuantity' => 0
        ];
    }

    $groupedAssignments[$locationName]['items'][] = $assignment;
    $groupedAssignments[$locationName]['totalQuantity'] += (int)$assignment['QuantityAtLocation'];
}

if (!empty($groupedAssignments)):
    foreach ($groupedAssignments as $locationName => $locationData): ?>
        <div class="location-section">
            <div class="location-header">
                <h3><?php echo htmlspecialchars($locationName); ?></h3>
                <div class="location-total">
                    Total Quantity at Location: <?php echo htmlspecialchars($locationData['totalQuantity']); ?>
                </div>
            </div>

            <table>
                <tr>
                    <th>Item</th>
                    <th>SKU</th>
                    <th>Quantity</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>

                <?php foreach ($locationData['items'] as $assignment): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($assignment['ItemName']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['SkuCode']); ?></td>
                        <td><?php echo htmlspecialchars($assignment['QuantityAtLocation']); ?></td>
                        <td>
                            <?php if ((int)$assignment['IsActive'] === 1): ?>
                                Active
                            <?php else: ?>
                                <span class="inactive-label">Inactive</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form action="deleteItemLocation.php" method="POST"
                                  onsubmit="return confirm('Are you sure you want to remove this item from this location?');">
                                <input type="hidden" name="item_location_id" value="<?php echo $assignment['ItemLocationID']; ?>">
                                <button type="submit" class="remove-btn">Remove</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    <?php endforeach;
else: ?>
    <div class="empty-note">
        No item-location assignments exist yet.
    </div>
<?php
endif;

$conn->close();
?>