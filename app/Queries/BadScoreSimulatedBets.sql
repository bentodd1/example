select * from simulated_bets s1
join game_betting_lines gbl on s1.nonSharpBettingLineId = gbl.id
join games g on g.id = gbl.gameId
join scores s on g.id = s.gameId
where s1.won is not null and homeTeamScore = 0;

ALTER TABLE simulated_bets DROP CONSTRAINT simulated_bets_scoreid_foreign;

delete  from scores s1
where s1.homeTeamScore = 0;

ALTER TABLE simulated_bets
ADD CONSTRAINT simulated_bets_scoreid_foreign
FOREIGN KEY (scoreId) REFERENCES scores (ID);

