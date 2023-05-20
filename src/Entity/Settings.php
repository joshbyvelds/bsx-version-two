<?php

namespace App\Entity;

use App\Repository\SettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingsRepository::class)]
class Settings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[ORM\Column(type: 'integer')]
    private $dashboard_transactions;

    #[ORM\OneToOne(inversedBy: 'settings', targetEntity: User::class, cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private $User;

    #[ORM\Column(type: 'integer')]
    private $max_play_money;

    #[ORM\Column(type: 'integer')]
    private $max_plays;

    #[ORM\Column(type: 'float')]
    private $futures_play_bucket_max;
 
    #[ORM\Column(type: 'float')]
    private $futures_profit_bucket_max;

    #[ORM\Column(type: 'boolean')]
    private $futures_use_split_profits;

    #[ORM\Column(type: 'integer')]
    private $futures_profit_split_level_1_amount;

    #[ORM\Column(type: 'float')]
    private $futures_profit_split_level_1_ratio;

    #[ORM\Column(type: 'integer')]
    private $futures_profit_split_level_2_amount;

    #[ORM\Column(type: 'float')]
    private $futures_profit_split_level_2_ratio;

    #[ORM\Column(type: 'integer')]
    private $futures_profit_split_level_3_amount;

    #[ORM\Column(type: 'float')]
    private $futures_profit_split_level_3_ratio;

    #[ORM\Column(type: 'integer')]
    private $futures_profit_split_level_4_amount;

    #[ORM\Column(type: 'float')]
    private $futures_profit_split_level_4_ratio;

    #[ORM\Column(type: 'integer')]
    private $futures_profit_split_level_5_amount;

    #[ORM\Column(type: 'float')]
    private $futures_profit_split_level_5_ratio;

    #[ORM\Column(type: 'integer')]
    private $futures_profit_split_level_6_amount;

    #[ORM\Column(type: 'float')]
    private $futures_profit_split_level_6_ratio;

    #[ORM\Column(type: 'integer')]
    private $futures_profit_split_level_7_amount;

    #[ORM\Column(type: 'float')]
    private $futures_profit_split_level_7_ratio;

    #[ORM\Column(type: 'boolean')]
    private $futures_use_broker_margin;

    #[ORM\Column(type: 'float')]
    private $futures_broker_margin_amount;

    #[ORM\Column(type: 'float')]
    private $futures_weekly_goal;

    #[ORM\Column(type: 'boolean')]
    private $stocks_update_sold_price;

    #[ORM\Column(type: 'boolean')]
    private $stocks_manual_update_enabled;

    #[ORM\Column(type: 'boolean')]
    private $stocks_disable_update_enabled;

    #[ORM\Column(type: 'boolean')]
    private $stocks_disable_canadian_update_enabled;

    #[ORM\Column(type: 'float')]
    private $futures_data_fee;

    #[ORM\Column(type: 'integer')]
    private $theme;

    #[ORM\Column(type: 'integer')]
    private $cash_equity_balance;

    #[ORM\Column(type: 'boolean')]
    private $dashboard_use_hot_cold_meter;

    #[ORM\Column(type: 'boolean')]
    private $dashboard_use_cash_equity_balance;

    #[ORM\Column(type: 'integer')]
    private $stocks_canadian_update_amount_limit;

    #[ORM\Column(type: 'integer')]
    private $stocks_canadian_update_limit_timeout;

    #[ORM\Column(type: 'boolean')]
    private $dashboard_portfolio_large;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDashboardTransactions(): ?int
    {
        return $this->dashboard_transactions;
    }

    public function setDashboardTransactions(int $dashboard_transactions): self
    {
        $this->dashboard_transactions = $dashboard_transactions;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->User;
    }

    public function setUser(User $User): self
    {
        $this->User = $User;

        return $this;
    }

    public function getMaxPlayMoney(): ?int
    {
        return $this->max_play_money;
    }

    public function setMaxPlayMoney(int $max_play_money): self
    {
        $this->max_play_money = $max_play_money;

        return $this;
    }

    public function getMaxPlays(): ?int
    {
        return $this->max_plays;
    }

    public function setMaxPlays(int $max_plays): self
    {
        $this->max_plays = $max_plays;

        return $this;
    }

    public function getFuturesPlayBucketMax(): ?float
    {
        return $this->futures_play_bucket_max;
    }

    public function setFuturesPlayBucketMax(float $futures_play_bucket_max): self
    {
        $this->futures_play_bucket_max = $futures_play_bucket_max;

        return $this;
    }

    public function getFuturesProfitBucketMax(): ?float
    {
        return $this->futures_profit_bucket_max;
    }

    public function setFuturesProfitBucketMax(float $futures_profit_bucket_max): self
    {
        $this->futures_profit_bucket_max = $futures_profit_bucket_max;

        return $this;
    }

    public function getFuturesUseSplitProfits(): ?bool
    {
        return $this->futures_use_split_profits;
    }

    public function setFuturesUseSplitProfits(bool $futures_use_split_profits): self
    {
        $this->futures_use_split_profits = $futures_use_split_profits;

        return $this;
    }

    public function getFuturesProfitSplitLevel1Amount(): ?int
    {
        return $this->futures_profit_split_level_1_amount;
    }

    public function setFuturesProfitSplitLevel1Amount(int $futures_profit_split_level_1_amount): self
    {
        $this->futures_profit_split_level_1_amount = $futures_profit_split_level_1_amount;

        return $this;
    }

    public function getFuturesProfitSplitLevel1Ratio(): ?float
    {
        return $this->futures_profit_split_level_1_ratio;
    }

    public function setFuturesProfitSplitLevel1Ratio(float $futures_profit_split_level_1_ratio): self
    {
        $this->futures_profit_split_level_1_ratio = $futures_profit_split_level_1_ratio;

        return $this;
    }

    public function getFuturesProfitSplitLevel2Amount(): ?int
    {
        return $this->futures_profit_split_level_2_amount;
    }

    public function setFuturesProfitSplitLevel2Amount(int $futures_profit_split_level_2_amount): self
    {
        $this->futures_profit_split_level_2_amount = $futures_profit_split_level_2_amount;

        return $this;
    }

    public function getFuturesProfitSplitLevel2Ratio(): ?float
    {
        return $this->futures_profit_split_level_2_ratio;
    }

    public function setFuturesProfitSplitLevel2Ratio(float $futures_profit_split_level_2_ratio): self
    {
        $this->futures_profit_split_level_2_ratio = $futures_profit_split_level_2_ratio;

        return $this;
    }

    public function getFuturesProfitSplitLevel3Amount(): ?int
    {
        return $this->futures_profit_split_level_3_amount;
    }

    public function setFuturesProfitSplitLevel3Amount(int $futures_profit_split_level_3_amount): self
    {
        $this->futures_profit_split_level_3_amount = $futures_profit_split_level_3_amount;

        return $this;
    }

    public function getFuturesProfitSplitLevel3Ratio(): ?float
    {
        return $this->futures_profit_split_level_3_ratio;
    }

    public function setFuturesProfitSplitLevel3Ratio(float $futures_profit_split_level_3_ratio): self
    {
        $this->futures_profit_split_level_3_ratio = $futures_profit_split_level_3_ratio;

        return $this;
    }

    public function getFuturesProfitSplitLevel4Amount(): ?int
    {
        return $this->futures_profit_split_level_4_amount;
    }

    public function setFuturesProfitSplitLevel4Amount(int $futures_profit_split_level_4_amount): self
    {
        $this->futures_profit_split_level_4_amount = $futures_profit_split_level_4_amount;

        return $this;
    }

    public function getFuturesProfitSplitLevel4Ratio(): ?float
    {
        return $this->futures_profit_split_level_4_ratio;
    }

    public function setFuturesProfitSplitLevel4Ratio(float $futures_profit_split_level_4_ratio): self
    {
        $this->futures_profit_split_level_4_ratio = $futures_profit_split_level_4_ratio;

        return $this;
    }

    public function getFuturesProfitSplitLevel5Amount(): ?int
    {
        return $this->futures_profit_split_level_5_amount;
    }

    public function setFuturesProfitSplitLevel5Amount(int $futures_profit_split_level_5_amount): self
    {
        $this->futures_profit_split_level_5_amount = $futures_profit_split_level_5_amount;

        return $this;
    }

    public function getFuturesProfitSplitLevel5Ratio(): ?float
    {
        return $this->futures_profit_split_level_5_ratio;
    }

    public function setFuturesProfitSplitLevel5Ratio(float $futures_profit_split_level_5_ratio): self
    {
        $this->futures_profit_split_level_5_ratio = $futures_profit_split_level_5_ratio;

        return $this;
    }

    public function getFuturesProfitSplitLevel6Amount(): ?int
    {
        return $this->futures_profit_split_level_6_amount;
    }

    public function setFuturesProfitSplitLevel6Amount(int $futures_profit_split_level_6_amount): self
    {
        $this->futures_profit_split_level_6_amount = $futures_profit_split_level_6_amount;

        return $this;
    }

    public function getFuturesProfitSplitLevel6Ratio(): ?float
    {
        return $this->futures_profit_split_level_6_ratio;
    }

    public function setFuturesProfitSplitLevel6Ratio(float $futures_profit_split_level_6_ratio): self
    {
        $this->futures_profit_split_level_6_ratio = $futures_profit_split_level_6_ratio;

        return $this;
    }

    public function getFuturesProfitSplitLevel7Amount(): ?int
    {
        return $this->futures_profit_split_level_7_amount;
    }

    public function setFuturesProfitSplitLevel7Amount(int $futures_profit_split_level_7_amount): self
    {
        $this->futures_profit_split_level_7_amount = $futures_profit_split_level_7_amount;

        return $this;
    }

    public function getFuturesProfitSplitLevel7Ratio(): ?float
    {
        return $this->futures_profit_split_level_7_ratio;
    }

    public function setFuturesProfitSplitLevel7Ratio(float $futures_profit_split_level_7_ratio): self
    {
        $this->futures_profit_split_level_7_ratio = $futures_profit_split_level_7_ratio;

        return $this;
    }

    public function isFuturesUseBrokerMargin(): ?bool
    {
        return $this->futures_use_broker_margin;
    }

    public function setFuturesUseBrokerMargin(bool $futures_use_broker_margin): self
    {
        $this->futures_use_broker_margin = $futures_use_broker_margin;

        return $this;
    }

    public function getFuturesBrokerMarginAmount(): ?float
    {
        return $this->futures_broker_margin_amount;
    }

    public function setFuturesBrokerMarginAmount(float $futures_broker_margin_amount): self
    {
        $this->futures_broker_margin_amount = $futures_broker_margin_amount;

        return $this;
    }

    public function getFuturesWeeklyGoal(): ?float
    {
        return $this->futures_weekly_goal;
    }

    public function setFuturesWeeklyGoal(float $futures_weekly_goal): self
    {
        $this->futures_weekly_goal = $futures_weekly_goal;

        return $this;
    }

    public function isStocksUpdateSoldPrice(): ?bool
    {
        return $this->stocks_update_sold_price;
    }

    public function setStocksUpdateSoldPrice(bool $stocks_update_sold_price): self
    {
        $this->stocks_update_sold_price = $stocks_update_sold_price;

        return $this;
    }

    public function isStocksManualUpdateEnabled(): ?bool
    {
        return $this->stocks_manual_update_enabled;
    }

    public function setStocksManualUpdateEnabled(bool $stocks_manual_update_enabled): self
    {
        $this->stocks_manual_update_enabled = $stocks_manual_update_enabled;

        return $this;
    }

    public function isStocksDisableUpdateEnabled(): ?bool
    {
        return $this->stocks_disable_update_enabled;
    }

    public function setStocksDisableUpdateEnabled(bool $stocks_disable_update_enabled): self
    {
        $this->stocks_disable_update_enabled = $stocks_disable_update_enabled;

        return $this;
    }

    public function isStocksDisableCanadianUpdateEnabled(): ?bool
    {
        return $this->stocks_disable_canadian_update_enabled;
    }

    public function setStocksDisableCanadianUpdateEnabled(bool $stocks_disable_canadian_update_enabled): self
    {
        $this->stocks_disable_canadian_update_enabled = $stocks_disable_canadian_update_enabled;

        return $this;
    }

    public function getFuturesDataFee(): ?float
    {
        return $this->futures_data_fee;
    }

    public function setFuturesDataFee(float $futures_data_fee): self
    {
        $this->futures_data_fee = $futures_data_fee;

        return $this;
    }

    public function getTheme(): ?int
    {
        return $this->theme;
    }

    public function setTheme(int $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    public function getCashEquityBalance(): ?int
    {
        return $this->cash_equity_balance;
    }

    public function setCashEquityBalance(int $cash_equity_balance): self
    {
        $this->cash_equity_balance = $cash_equity_balance;

        return $this;
    }

    public function isDashboardUseHotColdMeter(): ?bool
    {
        return $this->dashboard_use_hot_cold_meter;
    }

    public function setDashboardUseHotColdMeter(bool $dashboard_use_hot_cold_meter): self
    {
        $this->dashboard_use_hot_cold_meter = $dashboard_use_hot_cold_meter;

        return $this;
    }

    public function isDashboardUseCashEquityBalance(): ?bool
    {
        return $this->dashboard_use_cash_equity_balance;
    }

    public function setDashboardUseCashEquityBalance(bool $dashboard_use_cash_equity_balance): self
    {
        $this->dashboard_use_cash_equity_balance = $dashboard_use_cash_equity_balance;

        return $this;
    }

    public function getStocksCanadianUpdateAmountLimit(): ?int
    {
        return $this->stocks_canadian_update_amount_limit;
    }

    public function setStocksCanadianUpdateAmountLimit(int $stocks_canadian_update_amount_limit): self
    {
        $this->stocks_canadian_update_amount_limit = $stocks_canadian_update_amount_limit;

        return $this;
    }

    public function getStocksCanadianUpdateLimitTimeout(): ?int
    {
        return $this->stocks_canadian_update_limit_timeout;
    }

    public function setStocksCanadianUpdateLimitTimeout(int $stocks_canadian_update_limit_timeout): self
    {
        $this->stocks_canadian_update_limit_timeout = $stocks_canadian_update_limit_timeout;

        return $this;
    }

    public function isDashboardPortfolioLarge(): ?bool
    {
        return $this->dashboard_portfolio_large;
    }

    public function setDashboardPortfolioLarge(bool $dashboard_portfolio_large): self
    {
        $this->dashboard_portfolio_large = $dashboard_portfolio_large;

        return $this;
    }


}
