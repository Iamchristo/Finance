import { createChart, ColorType } from 'lightweight-charts';

function initForexChart() {
    const container = document.getElementById('forex-chart');

    if (!container) {
        return;
    }

    const candles = JSON.parse(container.dataset.candles || '[]');

    const chart = createChart(container, {
        layout: {
            background: { type: ColorType.Solid, color: 'transparent' },
            textColor: '#94a3b8',
        },
        grid: {
            vertLines: { color: 'rgba(148, 163, 184, 0.08)' },
            horzLines: { color: 'rgba(148, 163, 184, 0.08)' },
        },
        width: container.clientWidth,
        height: 420,
        timeScale: { timeVisible: true },
    });

    const series = chart.addCandlestickSeries({
        upColor: '#16c784',
        downColor: '#ef4757',
        borderVisible: false,
        wickUpColor: '#16c784',
        wickDownColor: '#ef4757',
    });

    series.setData(candles.map((candle) => ({
        time: Math.floor(new Date(candle.bucket_start_at.replace(' ', 'T') + 'Z').getTime() / 1000),
        open: Number(candle.open),
        high: Number(candle.high),
        low: Number(candle.low),
        close: Number(candle.close),
    })));

    chart.timeScale().fitContent();

    window.addEventListener('resize', () => {
        chart.applyOptions({ width: container.clientWidth });
    });
}

document.addEventListener('DOMContentLoaded', initForexChart);
