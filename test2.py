from subprocess import run, PIPE, DEVNULL
from pathlib import Path

RED = '\033[91m' #]
BLUE = '\033[94m' #]
RESET = '\033[0m' #]
YELLOW = '\033[93m' #]
LIGHT_GREEN = '\033[92m' #]
MAGENTA = '\033[95m' #]

IC23INT_PATH = r'E:\skola\semester\IPP\project\ic23int\ic23int.exe'
INTERPRET_PATH = r'E:\skola\semester\IPP\project\ipp-core\interpret.php'
TRANSPILER_PATH = r'E:\skola\semester\IPP\project\parse.py'
TESTS_PATH = r'E:\skola\semester\IPP\project\tests2'

def main():
    passed_tests = 0
    failed_tests = 0

    for test_path in Path(TESTS_PATH).glob('*.src'):
        source_path = str(test_path)
        input_path = source_path.replace('.src', '.in')
        xml_path = source_path.replace('.src', '.xml')

        _input_path = Path(input_path)
        if _input_path.exists():
            input_bytes = _input_path.read_bytes()
        else:
            input_bytes = b''

        print(MAGENTA, f'# running test {test_path.name}', RESET)
        print(LIGHT_GREEN, '# transpiling to xml', RESET)

        cmd = ['python', TRANSPILER_PATH, '--header']
        print(*cmd, YELLOW)

        with open(xml_path, 'w') as xml_file, open(source_path, 'r') as source_file:
            transpiler_process = run(cmd, stdin=source_file, stderr=None, stdout=xml_file)

        if transpiler_process.returncode != 0:
            print(f'{RED}Transpiling failed for {test_path.name}{RESET}')
            failed_tests += 1
            continue

        print(LIGHT_GREEN, '# running interpret.php', RESET)

        cmd = ['php', INTERPRET_PATH, '--source=' + xml_path]
        print(*cmd, YELLOW)

        interpret_process = run(cmd, stdout=PIPE, stderr=None, input=input_bytes)

        print(LIGHT_GREEN, '# running ic23int', RESET)

        cmd = [IC23INT_PATH, source_path]
        print(*cmd, YELLOW)

        ic23int_process = run(cmd, stdout=PIPE, stderr=None, input=input_bytes)

        php_return = interpret_process.returncode
        c_return = ic23int_process.returncode

        if php_return != c_return:
            print(f'{RED}Return codes do not match: interpret.php={php_return}, ic23int={c_return}{RESET}')
            failed_tests += 1
            continue

        if interpret_process.stdout != ic23int_process.stdout:
            print(f'{RED}Outputs do not match for {test_path.name}{RESET}')
            failed_tests += 1
            continue

        print(f'{BLUE}Test {source_path} passed{RESET}')
        passed_tests += 1

    color = BLUE if failed_tests == 0 else RED
    print(f'{color}Passed tests: {passed_tests} / {passed_tests + failed_tests}{RESET}')

if __name__ == '__main__':
    main()
