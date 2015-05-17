<?php
	$eos_firm_is_public = 0;
	$eos_player_stock_percent = 0;
	$eos_player_stock_shares = 0;
	$eos_player_is_msh = 0;
	if($eos_firm_id){
		$query = $db->prepare("SELECT shares_os FROM firm_stock WHERE fid = ?");
		$query->execute(array($eos_firm_id));
		$shares_os = $query->fetchColumn();
		if($shares_os){
			$eos_firm_is_public = 1;
			$query = $db->prepare("SELECT shares FROM player_stock WHERE pid = ? AND fid = ?");
			$query->execute(array($eos_player_id, $eos_firm_id));
			$eos_player_stock_shares = $query->fetchColumn();
			if($eos_player_stock_shares){
				$eos_player_stock_percent = 100*$eos_player_stock_shares/$shares_os;
				$query = $db->prepare("SELECT pid FROM player_stock WHERE fid = ? ORDER BY shares DESC LIMIT 0,1");
				$query->execute(array($eos_firm_id));
				$majority_shareholder = $query->fetchColumn();
				if($majority_shareholder == $eos_player_id){
					$eos_player_is_msh = 1;
				}
			}
		}
	}
?>