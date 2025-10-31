#!/bin/bash
# train-course-bundle 测试执行脚本
# 使用并行测试优化性能，将测试时间从15+分钟降低到4分钟以内

set -e

# 切换到项目根目录
cd "$(dirname "$0")/../.."

# 颜色定义
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m' # No Color

echo -e "${GREEN}=== Train Course Bundle 测试套件 ===${NC}"
echo ""

# 默认使用8个并行进程（根据CPU核心数调整）
PROCESSES=${PROCESSES:-8}

# 检测CPU核心数
if command -v nproc &> /dev/null; then
    CPU_CORES=$(nproc)
elif command -v sysctl &> /dev/null; then
    CPU_CORES=$(sysctl -n hw.ncpu 2>/dev/null || echo 4)
else
    CPU_CORES=4
fi

# 如果未设置PROCESSES，使用CPU核心数
if [ -z "${PROCESSES+x}" ]; then
    PROCESSES=$CPU_CORES
    echo -e "${YELLOW}检测到 ${CPU_CORES} 个CPU核心，使用 ${PROCESSES} 个并行进程${NC}"
fi

echo -e "${YELLOW}测试配置:${NC}"
echo "  - 测试包: packages/train-course-bundle"
echo "  - 并行进程数: ${PROCESSES}"
echo "  - 总测试数: ~1791"
echo "  - 预期时间: 3-5分钟"
echo ""

# 检查 paratest 是否可用
if ! vendor/bin/paratest --version &> /dev/null; then
    echo -e "${RED}错误: paratest 未安装${NC}"
    echo "请运行: composer require --dev brianium/paratest"
    exit 1
fi

# 运行测试
echo -e "${GREEN}开始运行测试...${NC}"
echo ""

START_TIME=$(date +%s)

# 使用 paratest 并行执行
vendor/bin/paratest \
    --processes="${PROCESSES}" \
    --testdox \
    --colors \
    packages/train-course-bundle/tests

TEST_EXIT_CODE=$?
END_TIME=$(date +%s)
DURATION=$((END_TIME - START_TIME))

echo ""
echo -e "${GREEN}=== 测试完成 ===${NC}"
echo "  - 执行时间: ${DURATION} 秒"
echo "  - 并行进程: ${PROCESSES}"

if [ $TEST_EXIT_CODE -eq 0 ]; then
    echo -e "${GREEN}  - 状态: 通过 ✓${NC}"
else
    echo -e "${RED}  - 状态: 失败 ✗${NC}"
fi

exit $TEST_EXIT_CODE
