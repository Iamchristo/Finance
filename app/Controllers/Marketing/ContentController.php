<?php

declare(strict_types=1);

namespace App\Controllers\Marketing;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;

final class ContentController extends Controller
{
    public function faq(Request $request): Response
    {
        return $this->view('marketing/faq', [
            'title' => 'Frequently Asked Questions',
            'groups' => $this->faqGroups(),
        ]);
    }

    public function calculators(Request $request): Response
    {
        return $this->view('marketing/calculators', [
            'title' => 'Investment & Trading Calculators',
        ]);
    }

    public function blogIndex(Request $request): Response
    {
        return $this->view('marketing/blog/index', [
            'title' => 'Blog',
            'posts' => $this->posts(),
        ]);
    }

    public function blogShow(Request $request): Response
    {
        $slug = (string) $request->attribute('slug');
        $post = null;
        foreach ($this->posts() as $candidate) {
            if ($candidate['slug'] === $slug) {
                $post = $candidate;
                break;
            }
        }

        if ($post === null) {
            return Response::html('404 Not Found', 404);
        }

        return $this->view('marketing/blog/show', [
            'title' => $post['title'],
            'post' => $post,
        ]);
    }

    public function legalTerms(Request $request): Response
    {
        return $this->view('marketing/legal/terms', ['title' => 'Terms of Service']);
    }

    public function legalPrivacy(Request $request): Response
    {
        return $this->view('marketing/legal/privacy', ['title' => 'Privacy Policy']);
    }

    public function legalRiskDisclosure(Request $request): Response
    {
        return $this->view('marketing/legal/risk-disclosure', ['title' => 'Risk Disclosure']);
    }

    public function legalSimulatedDisclosure(Request $request): Response
    {
        return $this->view('marketing/legal/simulated-disclosure', ['title' => 'Simulated Platform Disclosure']);
    }

    /** @return array<int, array{question: string, answer: string}>[] keyed by group name */
    private function faqGroups(): array
    {
        return [
            'Platform' => [
                ['question' => 'Is this a real investment platform?', 'answer' => 'No. Meridian Capital is a simulated demonstration platform. All balances, prices, trades, and returns are virtual and exist only for showcasing the product experience. No real money is ever deposited, traded, or withdrawn.'],
                ['question' => 'What is the shared wallet?', 'answer' => 'Every account has a single wallet with four sub-balances — Main, Investment, Forex, and Real Estate. You can move simulated funds between them from the Wallet page, and each vertical draws from its own sub-balance.'],
                ['question' => 'Can I withdraw funds?', 'answer' => 'Because all balances are simulated, there are no real withdrawal rails. The platform focuses on demonstrating the investing, trading, and real estate experience end to end.'],
            ],
            'Investment' => [
                ['question' => 'How do investment plans accrue returns?', 'answer' => 'Each plan has a simulated ROI percentage and accrual period (daily, weekly, or monthly). A scheduled job credits accrued returns to your Investment balance on schedule for the life of your subscription.'],
                ['question' => 'What is compounding?', 'answer' => 'Plans that allow compounding let accrued returns be reinvested into the principal automatically, rather than paid out to your balance, for faster simulated growth.'],
            ],
            'Trading' => [
                ['question' => 'Are the market prices real?', 'answer' => 'No. Prices are produced by an internal random-walk price simulator, not live market data. Charts, order books, and price action are illustrative only.'],
                ['question' => 'What is leverage in this simulation?', 'answer' => 'Leverage multiplies the simulated exposure of a position relative to margin used, mirroring how real margin trading works, purely for demonstration.'],
            ],
            'Real Estate' => [
                ['question' => 'What does fractional ownership mean here?', 'answer' => 'Simulated properties are divided into shares. Investing purchases a number of shares at the listed share price from your Real Estate balance, and the property pays simulated periodic returns.'],
                ['question' => 'How does the marketplace work?', 'answer' => 'Users can list a simulated holding for sale; other users can submit offers, and the seller can accept, counter, or reject — settled instantly through the shared wallet.'],
            ],
        ];
    }

    /** @return array<int, array<string, mixed>> */
    private function posts(): array
    {
        return [
            [
                'slug' => 'understanding-tiered-investment-plans',
                'title' => 'Understanding Tiered Investment Plans',
                'excerpt' => 'A look at how starter, growth, premium, and elite plans differ in minimums, ROI, and duration.',
                'published_at' => '2026-01-12',
                'body' => "Tiered investment plans group offerings by risk and reward profile. Starter tiers carry lower minimums and more conservative simulated returns, while elite tiers require larger principal and target higher simulated ROI over longer durations.\n\nWhen evaluating a plan, look at three numbers together: the minimum and maximum contribution, the ROI percentage per accrual period, and the duration in days. A plan with a higher headline ROI but a much longer duration may produce a similar annualized return to a shorter, lower-ROI plan.\n\nCompounding plans reinvest each accrual back into the principal automatically, which means the accrual base grows over time rather than staying fixed — useful to understand before subscribing.",
            ],
            [
                'slug' => 'how-leverage-works-in-trading',
                'title' => 'How Leverage Works in Trading',
                'excerpt' => 'Leverage lets a trader control a larger position than their margin alone would allow — here is the mechanics.',
                'published_at' => '2026-02-03',
                'body' => "Leverage expresses the ratio between position size and the margin committed to open it. A 10x leveraged position on a $1,000 margin controls $10,000 of simulated exposure.\n\nThe upside is that price moves are amplified in your favor; the downside is that they are equally amplified against you. Platforms track margin level continuously, and when losses erode the posted margin below a maintenance threshold, a margin call or liquidation can occur.\n\nBecause all trading on this platform is simulated, leverage here illustrates the mechanics without financial risk — but the underlying math mirrors real margin trading.",
            ],
            [
                'slug' => 'fractional-real-estate-explained',
                'title' => 'Fractional Real Estate, Explained',
                'excerpt' => 'Buying a share of a property instead of the whole thing — what it means and how payouts work.',
                'published_at' => '2026-03-18',
                'body' => "Fractional real estate investing divides a property's total value into shares, each priced individually. Instead of purchasing an entire building, an investor buys however many shares fit their budget.\n\nProperties accrue simulated returns periodically based on their expected annual ROI, split proportionally across all shareholders. As more shares sell, the property's funding status moves from open toward funded.\n\nThe marketplace mode works differently: instead of pooled fractional ownership, a single listing represents an asset a user wants to sell outright, with offers and counter-offers negotiated directly between buyer and seller.",
            ],
            [
                'slug' => 'why-we-built-a-simulated-platform',
                'title' => 'Why We Built a Simulated Platform',
                'excerpt' => 'A note on why every balance, trade, and payout here is virtual by design.',
                'published_at' => '2026-04-22',
                'body' => "Meridian Capital exists to demonstrate what a unified investment, trading, and real estate product experience looks like under one account and one wallet. Building it as a simulation, rather than connecting to real custody, brokerage, or payment rails, let us focus entirely on product experience: dashboards, order flow, accrual mechanics, and marketplace negotiation.\n\nEvery page that shows a balance, a price, or a return carries a visible simulated-platform notice. Nothing here moves real money, and nothing here is investment advice.",
            ],
            [
                'slug' => 'reading-a-candlestick-chart',
                'title' => 'Reading a Candlestick Chart',
                'excerpt' => 'The basics of open, high, low, and close — and what a green or red candle tells you.',
                'published_at' => '2026-05-09',
                'body' => "Each candlestick summarizes price action over a fixed interval: open, high, low, and close. A green (or hollow) candle means the close was higher than the open over that interval; a red (or filled) candle means the opposite.\n\nThe thin lines above and below the candle body — the wicks — show the highest and lowest prices reached during the interval, even if the price returned to close near the open.\n\nStacking candles across timeframes (1 minute, 1 hour, 1 day) reveals trend and volatility patterns that a single price number never could.",
            ],
        ];
    }
}
